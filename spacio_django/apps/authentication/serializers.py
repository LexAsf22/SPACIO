# apps/authentication/serializers.py

from rest_framework import serializers
from django.contrib.auth.hashers import check_password, make_password
from apps.authentication.models import SpacioUser


class LoginSerializer(serializers.Serializer):
    """
    Validates login credentials.
    Supports passwords hashed by PHP's password_hash() (bcrypt $2y$).
    """

    email    = serializers.EmailField()
    password = serializers.CharField(write_only=True)

    def validate(self, data):
        email    = data.get("email", "").strip()
        password = data.get("password", "")

        # Find user by email
        try:
            user = SpacioUser.objects.get(email=email)
        except SpacioUser.DoesNotExist:
            raise serializers.ValidationError(
                {"detail": "Invalid email or password."}
            )

        if not user.is_active:
            raise serializers.ValidationError(
                {"detail": "This account has been deactivated."}
            )

        # ── PHP bcrypt compatibility ───────────────────────────────────────────
        # PHP uses $2y$, Python bcrypt uses $2b$ — they are identical algorithms.
        # We convert the prefix before checking.
        stored_hash = user.password

        if stored_hash.startswith("$2y$"):
            stored_hash = "$2b$" + stored_hash[4:]

        password_valid = False

        try:
            import bcrypt
            password_valid = bcrypt.checkpw(
                password.encode("utf-8"),
                stored_hash.encode("utf-8")
            )
        except ImportError:
            # Fallback for Django-hashed passwords (created via register API)
            password_valid = check_password(password, user.password)

        if not password_valid:
            raise serializers.ValidationError(
                {"detail": "Invalid email or password."}
            )

        data["user"] = user
        return data


class RegisterSerializer(serializers.ModelSerializer):
    """Validates and creates a new user account."""

    password = serializers.CharField(write_only=True, min_length=8)

    class Meta:
        model  = SpacioUser
        fields = [
            "name", "school_id", "email", "password",
            "role", "campus", "course", "department",
        ]

    def validate_email(self, value):
        if SpacioUser.objects.filter(email=value).exists():
            raise serializers.ValidationError("An account with this email already exists.")
        return value

    def validate_school_id(self, value):
        if SpacioUser.objects.filter(school_id=value).exists():
            raise serializers.ValidationError("An account with this ID already exists.")
        return value

    def validate_role(self, value):
        allowed = ["student", "teacher"]
        if value not in allowed:
            raise serializers.ValidationError(f"Role must be one of: {', '.join(allowed)}.")
        return value

    def create(self, validated_data):
        validated_data["password"] = make_password(validated_data["password"])
        return SpacioUser.objects.create(**validated_data)


class UserSerializer(serializers.ModelSerializer):
    """Read-only — returned inside the JWT login response."""

    class Meta:
        model  = SpacioUser
        fields = ["id", "name", "email", "role", "campus", "course", "department"]
        read_only_fields = fields