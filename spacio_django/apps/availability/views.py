# apps/availability/views.py

from rest_framework.views    import APIView
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated, AllowAny

from apps.reservations.models import Reservation
from apps.labs.models          import Laboratory, Equipment


# ── GET /api/v1/availability/labs/ ───────────────────────────────────────────
class LabAvailabilityView(APIView):
    """
    Replaces check_availability.php.
    Returns {available: true/false} for a given lab + date + time_slot.

    Query params:
      ?lab_id=<int>&date=<YYYY-MM-DD>&time_slot=<string>
    """
    permission_classes = [AllowAny]

    def get(self, request):
        lab_id    = request.query_params.get("lab_id")
        date      = request.query_params.get("date")
        time_slot = request.query_params.get("time_slot")

        if not all([lab_id, date, time_slot]):
            return Response({"available": True})

        conflict = Reservation.objects.filter(
            lab_id    = int(lab_id),
            date      = date,
            time_slot = time_slot,
        ).exclude(status="Rejected").exists()

        return Response({"available": not conflict})


# ── GET /api/v1/availability/equipment/ ──────────────────────────────────────
class EquipmentAvailabilityView(APIView):
    """
    Returns {available: true/false} for a given equipment + date + time_slot.

    Query params:
      ?equipment_id=<int>&date=<YYYY-MM-DD>&time_slot=<string>
    """
    permission_classes = [AllowAny]

    def get(self, request):
        equipment_id = request.query_params.get("equipment_id")
        date         = request.query_params.get("date")
        time_slot    = request.query_params.get("time_slot")

        if not all([equipment_id, date, time_slot]):
            return Response({"available": True})

        conflict = Reservation.objects.filter(
            equipment_id = int(equipment_id),
            date         = date,
            time_slot    = time_slot,
        ).exclude(status="Rejected").exists()

        return Response({"available": not conflict})


# ── GET /api/v1/availability/slots/ ──────────────────────────────────────────
class AvailableSlotsView(APIView):
    """
    Returns a list of time slots that are still available for a lab on a given date.

    Query params:
      ?lab_id=<int>&date=<YYYY-MM-DD>
    """
    permission_classes = [IsAuthenticated]

    # Define your campus time slots here
    ALL_SLOTS = [
        "07:00-08:00", "08:00-09:00", "09:00-10:00",
        "10:00-11:00", "11:00-12:00", "12:00-13:00",
        "13:00-14:00", "14:00-15:00", "15:00-16:00",
        "16:00-17:00", "17:00-18:00",
    ]

    def get(self, request):
        lab_id = request.query_params.get("lab_id")
        date   = request.query_params.get("date")

        if not all([lab_id, date]):
            return Response({"available_slots": self.ALL_SLOTS})

        booked = Reservation.objects.filter(
            lab_id = int(lab_id),
            date   = date,
        ).exclude(status="Rejected").values_list("time_slot", flat=True)

        available = [s for s in self.ALL_SLOTS if s not in booked]

        return Response({
            "lab_id":          lab_id,
            "date":            date,
            "available_slots": available,
            "booked_slots":    list(booked),
        })

# ── GET /api/v1/availability/equipment/list/ ─────────────────────────────────
class EquipmentListAvailabilityView(APIView):
    """Returns all available equipment for the reservation dropdown."""
    permission_classes = [IsAuthenticated]

    def get(self, request):
        equipment = Equipment.objects.select_related("lab").filter(
            status="Available"
        ).order_by("lab__lab_name", "equipment_name")

        results = [
            {
                "id":             e.id,
                "equipment_name": e.equipment_name,
                "lab_name":       e.lab.lab_name if e.lab else "—",
                "campus":         e.lab.campus   if e.lab else "Other",
                "quantity":       e.quantity,
                "status":         e.status,
            }
            for e in equipment
        ]

        return Response(results)