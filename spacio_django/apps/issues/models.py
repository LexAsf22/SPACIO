# apps/issues/models.py

from django.db import models
from apps.authentication.models import SpacioUser


class Issue(models.Model):
    """Maps to the existing PHP `issues` table."""

    STATUS_CHOICES   = [("Pending", "Pending"), ("In Progress", "In Progress"), ("Done", "Done")]
    PRIORITY_CHOICES = [("Low", "Low"), ("Medium", "Medium"), ("High", "High")]
    CATEGORY_CHOICES = [("Equipment", "Equipment"), ("Facility", "Facility"), ("Software", "Software")]

    user        = models.ForeignKey(
        SpacioUser,
        on_delete    = models.CASCADE,
        db_column    = "user_id",
        related_name = "issues",
    )
    campus      = models.CharField(max_length=255)
    room        = models.CharField(max_length=255)
    category    = models.CharField(max_length=50,  choices=CATEGORY_CHOICES)
    description = models.TextField(blank=True, default="")
    priority    = models.CharField(max_length=20,  choices=PRIORITY_CHOICES, default="Low")
    status      = models.CharField(max_length=20,  choices=STATUS_CHOICES,   default="Pending")
    created_at  = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = "issues"
        managed  = False
        ordering = ["-created_at"]

    def __str__(self):
        return f"Issue #{self.id} — {self.category} ({self.status})"