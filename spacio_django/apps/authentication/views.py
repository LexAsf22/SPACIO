# apps/authentication/views.py

from rest_framework                             import status
from rest_framework.views                       import APIView
from rest_framework.response                    import Response
from rest_framework.permissions                 import AllowAny, IsAuthenticated
from rest_framework_simplejwt.tokens            import RefreshToken
from rest_framework_simplejwt.exceptions        import TokenError

from apps.authentication.serializers import (
    LoginSerializer,
    RegisterSerializer,
    UserSerializer,
)


def get_tokens_for_user(user):
    """Generate a JWT access + refresh token pair for a given user."""
    refresh = RefreshToken.for_user(user)

    # Embed extra claims so PHP can read role without another API call
    refresh["user_id"] = user.id
    refresh["role"]    = user.role
    refresh["name"]    = user.name

    return {
        "refresh": str(refresh),
        "access":  str(refresh.access_token),
    }


# ── POST /api/v1/auth/login/ ──────────────────────────────────────────────────
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


# ── POST /api/v1/auth/register/ ───────────────────────────────────────────────
class RegisterView(APIView):
    """
    Accepts registration fields.
    Creates the user in the shared database.
    Returns 201 on success — PHP then redirects to login.
    """
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = RegisterSerializer(data=request.data)

        if not serializer.is_valid():
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

        user = serializer.save()

        return Response({
            "message": "Account created successfully.",
            "user":    UserSerializer(user).data,
        }, status=status.HTTP_201_CREATED)


# ── POST /api/v1/auth/logout/ ─────────────────────────────────────────────────
class LogoutView(APIView):
    """
    Blacklists the refresh token so it can't be reused after logout.
    PHP calls this before destroying its own session.
    """
    permission_classes = [IsAuthenticated]

    def post(self, request):
        refresh_token = request.data.get("refresh")

        if not refresh_token:
            return Response(
                {"detail": "Refresh token is required."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        try:
            token = RefreshToken(refresh_token)
            token.blacklist()
        except TokenError:
            # Token already blacklisted or invalid — still OK to log out
            pass

        return Response(
            {"message": "Logged out successfully."},
            status=status.HTTP_200_OK,
        )


# ── GET /api/v1/auth/me/ ──────────────────────────────────────────────────────
class MeView(APIView):
    """Returns the currently authenticated user's profile."""
    permission_classes = [IsAuthenticated]

    def get(self, request):
        return Response(
            UserSerializer(request.user).data,
            status=status.HTTP_200_OK,
        )