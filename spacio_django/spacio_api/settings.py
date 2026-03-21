"""
spacio_api/settings.py
Spacio — Django Settings
"""

import os
import pymysql
from pathlib import Path
from datetime import timedelta
from dotenv import load_dotenv

# ── PyMySQL as MySQL driver (for XAMPP on Windows) ────────────────────────────
pymysql.install_as_MySQLdb()

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
    "apps.labs",
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