# apps/authentication/urls.py

from django.urls import path
from apps.authentication.views import (
    LoginView,
    RegisterView,
    LogoutView,
    MeView,
    PasswordResetRequestView,   # [NEW]
    PasswordResetConfirmView,   # [NEW]
)

urlpatterns = [
    # POST   /api/v1/auth/login/
    path("login/",           LoginView.as_view(),                name="auth-login"),

    # POST   /api/v1/auth/register/
    path("register/",        RegisterView.as_view(),             name="auth-register"),

    # POST   /api/v1/auth/logout/
    path("logout/",          LogoutView.as_view(),               name="auth-logout"),

    # GET    /api/v1/auth/me/
    path("me/",              MeView.as_view(),                   name="auth-me"),

    # POST   /api/v1/auth/forgot-password/   [NEW]
    path("forgot-password/", PasswordResetRequestView.as_view(), name="auth-forgot-password"),

    # POST   /api/v1/auth/reset-password/    [NEW]
    path("reset-password/",  PasswordResetConfirmView.as_view(), name="auth-reset-password"),
]