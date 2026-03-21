# apps/reservations/models.py

from django.db import models
from apps.authentication.models import SpacioUser
from apps.labs.models            import Laboratory, Equipment


class Reservation(models.Model):
    """Maps to the existing PHP `reservations` table."""

    STATUS_CHOICES = [
        ("Pending",  "Pending"),
        ("Approved", "Approved"),
        ("Rejected", "Rejected"),
    ]

    user      = models.ForeignKey(
        SpacioUser,
        on_delete    = models.CASCADE,
        db_column    = "user_id",
        related_name = "reservations",
    )
    lab       = models.ForeignKey(
        Laboratory,
        on_delete    = models.SET_NULL,
        null=True, blank=True,
        db_column    = "lab_id",
        related_name = "reservations",
    )
    equipment = models.ForeignKey(
        Equipment,
        on_delete    = models.SET_NULL,
        null=True, blank=True,
        db_column    = "equipment_id",
        related_name = "reservations",
    )
    date      = models.DateField()
    time_slot = models.CharField(max_length=100)
    status    = models.CharField(max_length=20, choices=STATUS_CHOICES, default="Pending")
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = "reservations"
        managed  = False
        ordering = ["-date", "time_slot"]

    def __str__(self):
        return f"Reservation #{self.id} — {self.user.name} ({self.status})"