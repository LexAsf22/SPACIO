# apps/labs/serializers.py

from rest_framework import serializers
from apps.labs.models import Laboratory, Equipment


class LaboratorySerializer(serializers.ModelSerializer):
    class Meta:
        model  = Laboratory
        fields = ["id", "lab_name", "campus", "capacity", "lab_type", "status", "image"]


class EquipmentSerializer(serializers.ModelSerializer):
    lab_name = serializers.CharField(source="lab.lab_name", read_only=True)
    lab_id   = serializers.IntegerField()

    class Meta:
        model  = Equipment
        fields = ["id", "equipment_name", "lab_id", "lab_name", "quantity", "status"]