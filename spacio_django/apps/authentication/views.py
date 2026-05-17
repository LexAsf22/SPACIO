# apps/authentication/views.py
# MODIFIED: Added PasswordResetRequestView and PasswordResetConfirmView
# Lines marked [NEW] are additions to the original file.

from rest_framework                             import status
from rest_framework.views                       import APIView
from rest_framework.response                    import Response
from rest_framework.permissions                 import AllowAny, IsAuthenticated
from rest_framework_simplejwt.tokens            import RefreshToken
from rest_framework_simplejwt.settings          import api_settings
from rest_framework_simplejwt.exceptions        import TokenError

from django.utils                               import timezone
from django.core.mail                           import send_mail
from django.conf                                import settings
from django.contrib.auth.hashers                import make_password

import secrets                                  # [NEW]
import bcrypt                                   # [NEW]

from apps.authentication.serializers import (
    LoginSerializer,
    RegisterSerializer,
    UserSerializer,
)


def get_tokens_for_user(user):
    """Generate a JWT access + refresh token pair for a given user."""
    refresh = RefreshToken()

    refresh["user_id"] = user.id
    refresh["role"]    = user.role
    refresh["name"]    = user.name
    refresh[api_settings.USER_ID_CLAIM] = user.id

    return {
        "refresh": str(refresh),
        "access":  str(refresh.access_token),
    }


# ── POST /api/v1/auth/login/ ─────────────────────────────────────────────────
class LoginView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = LoginSerializer(
            data    = request.data,
            context = {"request": request},
        )

        if not serializer.is_valid():
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

        user   = serializer.validated_data["user"]
        tokens = get_tokens_for_user(user)

        return Response({
            **tokens,
            "user": UserSerializer(user).data,
        }, status=status.HTTP_200_OK)


# ── POST /api/v1/auth/register/ ──────────────────────────────────────────────
class RegisterView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = RegisterSerializer(data=request.data)

        if not serializer.is_valid():
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

        user = serializer.save()

        try:
            user.agreed_to_terms    = True
            user.agreed_to_terms_at = timezone.now()
            user.save(update_fields=["agreed_to_terms", "agreed_to_terms_at"])
        except Exception:
            pass

        return Response({
            "message": "Account created successfully.",
            "user":    UserSerializer(user).data,
        }, status=status.HTTP_201_CREATED)


# ── POST /api/v1/auth/logout/ ────────────────────────────────────────────────
class LogoutView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        return Response(
            {"message": "Logged out successfully."},
            status=status.HTTP_200_OK,
        )


# ── GET /api/v1/auth/me/ ─────────────────────────────────────────────────────
class MeView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request):
        return Response(
            UserSerializer(request.user).data,
            status=status.HTTP_200_OK,
        )


# ── POST /api/v1/auth/forgot-password/ ───────────────────────────────────────
# [NEW]
class PasswordResetRequestView(APIView):
    """
    Accepts an email address.
    Generates a secure token, saves it to password_reset_tokens,
    and sends a reset link to the user's email via Mailtrap.
    Always returns 200 to avoid leaking whether an email exists.
    """
    permission_classes = [AllowAny]

    def post(self, request):
        from apps.authentication.models import SpacioUser
        from django.db import connection

        email = request.data.get("email", "").strip()

        if not email:
            return Response(
                {"message": "Email is required."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        # Always return 200 even if email not found (security best practice)
        try:
            user = SpacioUser.objects.get(email=email)
        except SpacioUser.DoesNotExist:
            return Response(
                {"message": "If that email exists, a reset link has been sent."},
                status=status.HTTP_200_OK,
            )

        # Generate a secure random token
        token      = secrets.token_hex(32)
        expires_at = timezone.now() + timezone.timedelta(hours=1)

        # Invalidate any existing unused tokens for this user
        with connection.cursor() as cursor:
            cursor.execute(
                "UPDATE `password_reset_tokens` SET `used` = 1 WHERE `user_id` = %s AND `used` = 0",
                [user.id],
            )

        # Insert new token
        with connection.cursor() as cursor:
            cursor.execute(
                """
                INSERT INTO `password_reset_tokens` (`user_id`, `token`, `expires_at`, `used`)
                VALUES (%s, %s, %s, 0)
                """,
                [user.id, token, expires_at],
            )

        # Build reset link
        base_url   = getattr(settings, "FRONTEND_BASE_URL", "http://localhost/spacio")
        reset_link = f"{base_url}/reset_password.php?token={token}"

        # Send email via Mailtrap
        try:
            import smtplib
            from email.mime.text import MIMEText

            msg = MIMEText(
                f"Hi {user.name},\n\n"
                f"You requested a password reset for your Spacio account.\n\n"
                f"Click the link below to reset your password (valid for 1 hour):\n"
                f"{reset_link}\n\n"
                f"If you did not request this, you can safely ignore this email.\n\n"
                f"— The Spacio Team"
            )
            msg["Subject"] = "Reset your Spacio password"
            msg["From"]    = settings.DEFAULT_FROM_EMAIL
            msg["To"]      = user.email

            import ssl
            ctx = ssl.SSLContext(ssl.PROTOCOL_TLS_CLIENT)
            ctx.check_hostname = False
            ctx.verify_mode = ssl.CERT_NONE

            with smtplib.SMTP(settings.EMAIL_HOST, settings.EMAIL_PORT) as server:
                server.ehlo()
                server.starttls(context=ctx)
                server.ehlo()
                server.login(settings.EMAIL_HOST_USER, settings.EMAIL_HOST_PASSWORD)
                server.sendmail(settings.DEFAULT_FROM_EMAIL, [user.email], msg.as_string())
        except Exception as e:
            import logging
            logging.getLogger(__name__).error(f"Failed to send reset email: {e}")

        return Response(
            {"message": "If that email exists, a reset link has been sent."},
            status=status.HTTP_200_OK,
        )


# ── POST /api/v1/auth/reset-password/ ────────────────────────────────────────
# [NEW]
class PasswordResetConfirmView(APIView):
    """
    Accepts token + new_password.
    Validates the token, hashes the new password using bcrypt ($2b$),
    updates the users table, and marks the token as used.
    """
    permission_classes = [AllowAny]

    def post(self, request):
        from apps.authentication.models import SpacioUser
        from django.db import connection

        token        = request.data.get("token", "").strip()
        new_password = request.data.get("new_password", "")
        confirm      = request.data.get("confirm_password", "")

        if not token or not new_password or not confirm:
            return Response(
                {"message": "Token, new password, and confirmation are required."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        if new_password != confirm:
            return Response(
                {"message": "Passwords do not match."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        if len(new_password) < 8:
            return Response(
                {"message": "Password must be at least 8 characters."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        # Look up the token
        with connection.cursor() as cursor:
            cursor.execute(
                """
                SELECT `user_id`, `expires_at`, `used`
                FROM `password_reset_tokens`
                WHERE `token` = %s
                LIMIT 1
                """,
                [token],
            )
            row = cursor.fetchone()

        if not row:
            return Response(
                {"message": "Invalid or expired reset link."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        user_id, expires_at, used = row

        if used:
            return Response(
                {"message": "This reset link has already been used."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        # expires_at is stored as UTC in DB, comes back naive — attach UTC directly
        now = timezone.now()
        if timezone.is_naive(expires_at):
            import pytz
            expires_at = expires_at.replace(tzinfo=pytz.UTC)

        if now > expires_at:
            return Response(
                {"message": "This reset link has expired. Please request a new one."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        # Hash new password with bcrypt ($2b$) so PHP can verify it
        hashed = bcrypt.hashpw(
            new_password.encode("utf-8"),
            bcrypt.gensalt(),
        ).decode("utf-8")

        # Update user password
        with connection.cursor() as cursor:
            cursor.execute(
                "UPDATE `users` SET `password` = %s WHERE `id` = %s",
                [hashed, user_id],
            )
            rows_affected = cursor.rowcount

        if rows_affected == 0:
            return Response(
                {"message": "User account not found."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        # Mark ALL tokens for this user as used
        with connection.cursor() as cursor:
            cursor.execute(
                "UPDATE `password_reset_tokens` SET `used` = 1 WHERE `user_id` = %s",
                [user_id],
            )

        return Response(
            {"message": "Password reset successfully. You can now log in."},
            status=status.HTTP_200_OK,
        )