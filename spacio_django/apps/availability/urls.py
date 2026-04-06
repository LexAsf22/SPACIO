# apps/availability/urls.py

from django.urls import path
from apps.availability.views import (
    LabAvailabilityView,
    EquipmentAvailabilityView,
    AvailableSlotsView,
    EquipmentListAvailabilityView,
)

urlpatterns = [
    # GET /api/v1/availability/labs/       ← replaces check_availability.php
    path("labs/",      LabAvailabilityView.as_view(),       name="lab-availability"),

    # GET /api/v1/availability/equipment/  ← check equipment availability
    path("equipment/", EquipmentAvailabilityView.as_view(), name="equipment-availability"),

    # GET /api/v1/availability/slots/      ← list open slots for a lab on a date
    path("slots/",     AvailableSlotsView.as_view(),        name="available-slots"),
    path("equipment/list/", EquipmentListAvailabilityView.as_view(), name="equipment-list-availability"),
]