"""
spacio_api/settings.py
Spacio — Django Settings
"""

import os
from pathlib import Path
from datetime import timedelta
from dotenv import load_dotenv

# ── PyMySQL as MySQL driver (for XAMPP on Windows) ────────────────────────────

# ── Load .env file ─────────────────────────────────────────────────────────────
BASE_DIR = Path(__file__).resolve().parent.parent
load_dotenv(BASE_DIR / ".env")

# ── Core ───────────────────────────────────────────────────────────────────────
SECRET_KEY  = os.getenv("SECRET_KEY", "django-insecure-change-this-in-production")
DEBUG       = os.getenv("DEBUG", "True") == "True"
ALLOWED_HOSTS = [h.strip() for h in os.getenv("ALLOWED_HOSTS", "localhost,127.0.0.1").split(",")]

# ── Installed apps ─────────────────────────────────────────────────────────────
INSTALLED_APPS = [
    "django.contrib.admin",
    "django.contrib.auth",
    "django.contrib.contenttypes",
    "django.contrib.sessions",
    "django.contrib.messages",
    "django.contrib.staticfiles",

    # Third-party
    "rest_framework",
    "rest_framework_simplejwt",
    "rest_framework_simplejwt.token_blacklist",
    "corsheaders",
    "drf_spectacular",

    # Spacio apps
    "apps.authentication",
    "apps.reservations",
    "apps.availability",
    "apps.issues",
    "apps.reports",
    "apps.labs"
]

# ── Middleware ─────────────────────────────────────────────────────────────────
MIDDLEWARE = [
    "corsheaders.middleware.CorsMiddleware",
    "django.middleware.security.SecurityMiddleware",
    "django.contrib.sessions.middleware.SessionMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.csrf.CsrfViewMiddleware",
    "django.contrib.auth.middleware.AuthenticationMiddleware",
    "django.contrib.messages.middleware.MessageMiddleware",
    "django.middleware.clickjacking.XFrameOptionsMiddleware",
    "spacio_api.middleware.api_key.ApiKeyMiddleware",
]

ROOT_URLCONF = "spacio_api.urls"

TEMPLATES = [
    {
        "BACKEND": "django.template.backends.django.DjangoTemplates",
        "DIRS": [],
        "APP_DIRS": True,
        "OPTIONS": {
            "context_processors": [
                "django.template.context_processors.debug",
                "django.template.context_processors.request",
                "django.contrib.auth.context_processors.auth",
                "django.contrib.messages.context_processors.messages",
            ],
        },
    },
]

WSGI_APPLICATION = "spacio_api.wsgi.application"

# ── Database ───────────────────────────────────────────────────────────────────
DATABASES = {
    "default": {
        "ENGINE":   os.getenv("DB_ENGINE",   "django.db.backends.mysql"),
        "NAME":     os.getenv("DB_NAME",     "spacio_db"),
        "USER":     os.getenv("DB_USER",     "root"),
        "PASSWORD": os.getenv("DB_PASSWORD", ""),
        "HOST":     os.getenv("DB_HOST",     "localhost"),
        "PORT":     os.getenv("DB_PORT",     "3306"),
        "OPTIONS": {
            "charset":  "utf8mb4",
            "sql_mode": "STRICT_TRANS_TABLES",
        },
    }   
}

# ── Custom user model ──────────────────────────────────────────────────────────
AUTH_USER_MODEL = "authentication.SpacioUser"

# ── Password validation ────────────────────────────────────────────────────────
AUTH_PASSWORD_VALIDATORS = [
    {"NAME": "django.contrib.auth.password_validation.UserAttributeSimilarityValidator"},
    {"NAME": "django.contrib.auth.password_validation.MinimumLengthValidator"},
    {"NAME": "django.contrib.auth.password_validation.CommonPasswordValidator"},
    {"NAME": "django.contrib.auth.password_validation.NumericPasswordValidator"},
]

# ── Internationalization ───────────────────────────────────────────────────────
LANGUAGE_CODE = "en-us"
TIME_ZONE     = "Asia/Manila"
USE_I18N      = True
USE_TZ        = True

# ── Static files ───────────────────────────────────────────────────────────────
STATIC_URL  = "/static/"
STATIC_ROOT = BASE_DIR / "staticfiles"

DEFAULT_AUTO_FIELD = "django.db.models.BigAutoField"

# ── Django REST Framework ──────────────────────────────────────────────────────
REST_FRAMEWORK = {
    "DEFAULT_AUTHENTICATION_CLASSES": (
        "rest_framework_simplejwt.authentication.JWTAuthentication",
    ),
    "DEFAULT_PERMISSION_CLASSES": (
        "rest_framework.permissions.IsAuthenticated",
    ),
    "DEFAULT_SCHEMA_CLASS": "drf_spectacular.openapi.AutoSchema",
    "DEFAULT_PAGINATION_CLASS": "rest_framework.pagination.PageNumberPagination",
    "PAGE_SIZE": 10,
}

# ── JWT ────────────────────────────────────────────────────────────────────────
SIMPLE_JWT = {
    "ACCESS_TOKEN_LIFETIME":    timedelta(minutes=int(os.getenv("JWT_ACCESS_TOKEN_LIFETIME",  "15"))),
    "REFRESH_TOKEN_LIFETIME":   timedelta(days=int(   os.getenv("JWT_REFRESH_TOKEN_LIFETIME", "7"))),
    "ROTATE_REFRESH_TOKENS":    True,
    "BLACKLIST_AFTER_ROTATION": True,
    "SIGNING_KEY":              os.getenv("JWT_SIGNING_KEY", SECRET_KEY),
    "AUTH_HEADER_TYPES":        ("Bearer",),
    "USER_ID_FIELD":            "id",
    "USER_ID_CLAIM":            "user_id",
}

# ── CORS ───────────────────────────────────────────────────────────────────────
CORS_ALLOWED_ORIGINS = [
    o.strip()
    for o in os.getenv(
        "CORS_ALLOWED_ORIGINS",
        "http://localhost,http://127.0.0.1,http://localhost:8080"
    ).split(",")
]
CORS_ALLOW_CREDENTIALS = True

# ── Shared API key ─────────────────────────────────────────────────────────────
INTERNAL_API_KEY = os.getenv("INTERNAL_API_KEY", "")

# ── Swagger / OpenAPI ──────────────────────────────────────────────────────────
SPECTACULAR_SETTINGS = {
    "TITLE":       "Spacio API",
    "DESCRIPTION": "REST API for the Spacio campus lab management system.",
    "VERSION":     "1.0.0",
}

# ── Email (Mailtrap sandbox) ───────────────────────────────────────────────────
# Add these lines to the BOTTOM of your spacio_api/settings.py
# Also add these vars to your .env file (see below)

EMAIL_BACKEND       = "django.core.mail.backends.smtp.EmailBackend"
EMAIL_HOST          = os.getenv("EMAIL_HOST",     "sandbox.smtp.mailtrap.io")
EMAIL_PORT          = int(os.getenv("EMAIL_PORT", "2525"))
EMAIL_HOST_USER     = os.getenv("EMAIL_HOST_USER", "")
EMAIL_HOST_PASSWORD = os.getenv("EMAIL_HOST_PASSWORD", "")
EMAIL_USE_TLS       = False
EMAIL_USE_SSL       = False
DEFAULT_FROM_EMAIL  = os.getenv("DEFAULT_FROM_EMAIL", "noreply@spacio.app")

# Frontend base URL used to build the reset link in emails
FRONTEND_BASE_URL   = os.getenv("FRONTEND_BASE_URL", "http://localhost/spacio")


# =============================================================================
# .env entries to add:
#
# EMAIL_HOST=sandbox.smtp.mailtrap.io
# EMAIL_PORT=2525
# EMAIL_HOST_USER=ffb9013144cf7c
# EMAIL_HOST_PASSWORD=542e163c651b92
# DEFAULT_FROM_EMAIL=noreply@spacio.app
# FRONTEND_BASE_URL=http://localhost/spacio
# =============================================================================