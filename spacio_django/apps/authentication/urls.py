# apps/authentication/urls.py

from django.urls import path
from apps.authentication.views import LoginView, RegisterView, LogoutView, MeView

urlpatterns = [
    # POST   /api/v1/auth/login/      ← called by PHP login.php
    path("login/",    LoginView.as_view(),    name="auth-login"),

    # POST   /api/v1/auth/register/   ← called by PHP register.php
    path("register/", RegisterView.as_view(), name="auth-register"),

    # POST   /api/v1/auth/logout/     ← called by PHP logout.php
    path("logout/",   LogoutView.as_view(),   name="auth-logout"),

    # GET    /api/v1/auth/me/         ← get current user profile
    path("me/",       MeView.as_view(),       name="auth-me"),
]