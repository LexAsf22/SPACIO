# apps/authentication/serializers.py
# MODIFIED: Added agreed_to_terms field to RegisterSerializer
# Lines marked [NEW] are additions to the original file.

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

        stored_hash = user.password

        if stored_hash.startswith("$2y$"):
            stored_hash = "$2b$" + stored_hash[4:]

        password_valid = False

        try:
            import bcrypt
            if stored_hash.startswith("$2"):
                password_valid = bcrypt.checkpw(
                    password.encode("utf-8"),
                    stored_hash.encode("utf-8")
                )
            else:
                password_valid = check_password(password, user.password)
        except (ImportError, ValueError):
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

    # [NEW] ── Terms & Conditions field ──────────────────────────────────────
    # write_only=True: we accept it on input but never expose it in responses.
    # It is NOT stored in the database (SpacioUser.managed=False, no column).
    # We validate it here and stamp agreed_to_terms_at in views.py.
    agreed_to_terms = serializers.BooleanField(
        write_only=True,
        required=True,
        error_messages={
            'required': 'You must agree to the Terms & Conditions and Data Privacy Policy.',
            'invalid':  'You must agree to the Terms & Conditions and Data Privacy Policy.',
        }
    )
    # ─────────────────────────────────────────────────────────────────────────

    class Meta:
        model  = SpacioUser
        fields = [
            "name", "school_id", "email", "password",
            "role", "campus", "course", "department",
            "agreed_to_terms",  # [NEW]
        ]

    # [NEW] ── Validate agreed_to_terms must be True (not just present) ──────
    def validate_agreed_to_terms(self, value):
        if not value:
            raise serializers.ValidationError(
                "You must accept the Terms & Conditions and Data Privacy Policy to register."
            )
        return value
    # ─────────────────────────────────────────────────────────────────────────

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
        validated_data.pop("agreed_to_terms", None)
        validated_data["password"] = make_password(validated_data["password"])

        # Use raw INSERT to avoid Django generating RETURNING `id`
        # which MariaDB does not support.
        from django.db import connection

        fields = ["name", "school_id", "email", "password",
                  "role", "campus", "course", "department",
                  "is_active", "is_staff", "is_superuser"]

        validated_data.setdefault("is_active",    True)
        validated_data.setdefault("is_staff",     False)
        validated_data.setdefault("is_superuser", False)
        validated_data.setdefault("course",       "")
        validated_data.setdefault("department",   "")
        validated_data.setdefault("campus",       "")

        columns = ", ".join(f"`{f}`" for f in fields)
        placeholders = ", ".join(["%s"] * len(fields))
        values = [validated_data[f] for f in fields]

        with connection.cursor() as cursor:
            cursor.execute(
                f"INSERT INTO `users` ({columns}) VALUES ({placeholders})",
                values,
            )
            user_id = cursor.lastrowid

        return SpacioUser.objects.get(pk=user_id)


class UserSerializer(serializers.ModelSerializer):
    """Read-only — returned inside the JWT login response."""

    class Meta:
        model  = SpacioUser
        fields = ["id", "name", "email", "role", "campus", "course", "department"]
        read_only_fields = fields