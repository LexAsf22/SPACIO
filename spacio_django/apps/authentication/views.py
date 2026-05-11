# apps/authentication/views.py
# MODIFIED: RegisterView now logs agreed_to_terms_at timestamp.
# Lines marked [NEW] are additions to the original file.

from rest_framework                             import status
from rest_framework.views                       import APIView
from rest_framework.response                    import Response
from rest_framework.permissions                 import AllowAny, IsAuthenticated
from rest_framework_simplejwt.tokens            import RefreshToken
from rest_framework_simplejwt.settings          import api_settings
from rest_framework_simplejwt.exceptions        import TokenError

from django.utils import timezone  # [NEW]

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
    """
    Accepts email + password.
    Returns JWT access token, refresh token, and user data.
    PHP stores the access token in $_SESSION['jwt'].
    """
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
    """
    Accepts registration fields including agreed_to_terms.
    Creates the user in the shared database.
    Returns 201 on success — PHP then redirects to login.
    """
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = RegisterSerializer(data=request.data)

        if not serializer.is_valid():
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

        user = serializer.save()

        # [NEW] ── Log the T&C acceptance timestamp ───────────────────────────
        # Note: agreed_to_terms and agreed_to_terms_at are OPTIONAL columns.
        # Only run this block if you've added those columns to your `users` table.
        # See the SQL migration snippet at the bottom of this file.
        try:
            user.agreed_to_terms    = True
            user.agreed_to_terms_at = timezone.now()
            user.save(update_fields=["agreed_to_terms", "agreed_to_terms_at"])
        except Exception:
            # If the columns don't exist yet, skip silently.
            # The serializer already validated the checkbox on the PHP side.
            pass
        # ─────────────────────────────────────────────────────────────────────

        return Response({
            "message": "Account created successfully.",
            "user":    UserSerializer(user).data,
        }, status=status.HTTP_201_CREATED)


# ── POST /api/v1/auth/logout/ ────────────────────────────────────────────────
class LogoutView(APIView):
    """
    Blacklists the refresh token so it can't be reused after logout.
    PHP calls this before destroying its own session.
    """
    permission_classes = [IsAuthenticated]

    def post(self, request):
        # Token blacklisting disabled — not supported on MariaDB 10.4.
        # Logout is handled by PHP destroying the session.
        return Response(
            {"message": "Logged out successfully."},
            status=status.HTTP_200_OK,
        )


# ── GET /api/v1/auth/me/ ─────────────────────────────────────────────────────
class MeView(APIView):
    """Returns the currently authenticated user's profile."""
    permission_classes = [IsAuthenticated]

    def get(self, request):
        return Response(
            UserSerializer(request.user).data,
            status=status.HTTP_200_OK,
        )


# =============================================================================
# [NEW] SQL MIGRATION — run this manually in your MySQL/MariaDB database
# (Do NOT use Django's manage.py migrate since SpacioUser has managed=False)
#
# Run in MySQL:
#
#   ALTER TABLE users
#       ADD COLUMN agreed_to_terms    TINYINT(1)   NOT NULL DEFAULT 0
#           AFTER department,
#       ADD COLUMN agreed_to_terms_at DATETIME     NULL DEFAULT NULL
#           AFTER agreed_to_terms;
#
# After running the SQL, remove the try/except in RegisterView.post above
# and let it run unconditionally.
# =============================================================================