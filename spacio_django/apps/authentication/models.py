# apps/authentication/models.py

from django.contrib.auth.models import AbstractBaseUser, BaseUserManager, PermissionsMixin
from django.db import models


class SpacioUserManager(BaseUserManager):
    """Custom manager for SpacioUser."""

    def create_user(self, email, password=None, **extra_fields):
        if not email:
            raise ValueError("Email is required.")
        email = self.normalize_email(email)
        user  = self.model(email=email, **extra_fields)
        user.set_password(password)
        user.save(using=self._db)
        return user

    def create_superuser(self, email, password=None, **extra_fields):
        extra_fields.setdefault("role",         "admin")
        extra_fields.setdefault("is_staff",     True)
        extra_fields.setdefault("is_superuser", True)
        return self.create_user(email, password, **extra_fields)


class SpacioUser(AbstractBaseUser, PermissionsMixin):
    """
    Custom user model that maps to the existing PHP `users` table.
    managed = False means Django reads/writes this table but never
    runs migrations that would ALTER or DROP it.
    """

    ROLE_CHOICES = [
        ("student", "Student"),
        ("teacher", "Teacher"),
        ("admin",   "Admin"),
    ]

    # ── Fields (must match the PHP users table columns exactly) ───────────────
    id           = models.AutoField(primary_key=True)
    name         = models.CharField(max_length=255)
    school_id    = models.CharField(max_length=100, unique=True)
    email        = models.EmailField(unique=True)
    password     = models.CharField(max_length=255)  # stores PHP password_hash()
    role         = models.CharField(max_length=20, choices=ROLE_CHOICES, default="student")
    campus       = models.CharField(max_length=255, blank=True, default="")
    course       = models.CharField(max_length=255, blank=True, default="")
    department   = models.CharField(max_length=255, blank=True, default="")
    is_active    = models.BooleanField(default=True)
    is_staff     = models.BooleanField(default=False)

    # ── Required by AbstractBaseUser ──────────────────────────────────────────
    last_login   = models.DateTimeField(null=True, blank=True)

    # ── Required by PermissionsMixin ──────────────────────────────────────────
    is_superuser = models.BooleanField(default=False)

    created_at   = models.DateTimeField(auto_now_add=True)

    objects = SpacioUserManager()

    USERNAME_FIELD  = "email"
    REQUIRED_FIELDS = ["name", "school_id"]

    class Meta:
        db_table = "users"   # point to the existing PHP table
        managed  = False     # Django will NOT run ALTER/DROP on this table

    def __str__(self):
        return f"{self.name} ({self.role})"

    def get_full_name(self):
        return self.name