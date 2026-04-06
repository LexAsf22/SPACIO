# apps/reservations/serializers.py

from rest_framework import serializers
from apps.reservations.models import Reservation


class ReservationSerializer(serializers.ModelSerializer):
    student_name   = serializers.CharField(source="user.name",           read_only=True)
    lab_name       = serializers.CharField(source="lab.lab_name",        read_only=True)
    equipment_name = serializers.CharField(source="equipment.equipment_name", read_only=True)

    class Meta:
        model  = Reservation
        fields = [
            "id", "user_id", "student_name",
            "lab_id", "lab_name",
            "equipment_id", "equipment_name",
            "date", "time_slot", "status", "created_at",
        ]
        read_only_fields = ["id", "status", "created_at"]


class ReservationCreateSerializer(serializers.ModelSerializer):
    time_slot    = serializers.CharField(required=False, allow_blank=True, allow_null=True)
    lab_id       = serializers.IntegerField(required=False, allow_null=True)
    equipment_id = serializers.IntegerField(required=False, allow_null=True)

    class Meta:
        model  = Reservation
        fields = ["user_id", "lab_id", "equipment_id", "date", "time_slot"]

    def validate(self, data):
        """Reject if same lab/time slot is already booked and not rejected."""
        lab_id    = data.get("lab_id")
        date      = data.get("date")
        time_slot = data.get("time_slot")

        if lab_id and date and time_slot:
            conflict = Reservation.objects.filter(
                lab_id    = lab_id,
                date      = date,
                time_slot = time_slot,
            ).exclude(status="Rejected").exists()

            if conflict:
                raise serializers.ValidationError(
                    {"time_slot": "This lab is already reserved at the selected date and time."}
                )
        return data