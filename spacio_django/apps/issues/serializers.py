# apps/issues/serializers.py

from rest_framework import serializers
from apps.issues.models import Issue


class IssueSerializer(serializers.ModelSerializer):
    teacher_name = serializers.CharField(source="user.name", read_only=True)

    class Meta:
        model  = Issue
        fields = [
            "id", "user_id", "teacher_name",
            "campus", "room", "category",
            "description", "priority", "status", "created_at",
        ]
        read_only_fields = ["id", "created_at"]


class IssueCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model  = Issue
        fields = ["user_id", "campus", "room", "category", "description", "priority"]