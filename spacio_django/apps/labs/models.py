# apps/labs/models.py

from django.db import models


class Laboratory(models.Model):
    """Maps to the existing PHP `laboratories` table."""

    lab_name  = models.CharField(max_length=255)
    campus    = models.CharField(max_length=255, blank=True, default="")
    capacity  = models.IntegerField(default=0)
    lab_type  = models.CharField(max_length=100, blank=True, default="")
    status    = models.CharField(max_length=50,  default="Available")
    image     = models.CharField(max_length=255, blank=True, default="")

    class Meta:
        db_table = "laboratories"
        managed  = False

    def __str__(self):
        return self.lab_name


class Equipment(models.Model):
    """Maps to the existing PHP `equipment` table."""

    STATUS_CHOICES = [
        ("Available",         "Available"),
        ("Under Maintenance", "Under Maintenance"),
        ("Unavailable",       "Unavailable"),
    ]

    equipment_name = models.CharField(max_length=255)
    lab            = models.ForeignKey(
        Laboratory,
        on_delete    = models.CASCADE,
        db_column    = "lab_id",
        related_name = "equipment",
    )
    quantity       = models.IntegerField(default=0)
    status         = models.CharField(
        max_length = 50,
        choices    = STATUS_CHOICES,
        default    = "Available",
    )

    class Meta:
        db_table = "equipment"
        managed  = False

    def __str__(self):
        return self.equipment_name