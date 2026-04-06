# apps/reports/views.py

from rest_framework.views    import APIView
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated

from apps.reservations.models import Reservation
from apps.issues.models        import Issue


# ── GET /api/v1/reports/reservations/ ────────────────────────────────────────
class ReservationReportView(APIView):
    """
    Returns reservation counts by status.
    Used by admin reports.php to populate the Chart.js bar chart.
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        return Response({
            "total":    Reservation.objects.count(),
            "approved": Reservation.objects.filter(status="Approved").count(),
            "rejected": Reservation.objects.filter(status="Rejected").count(),
            "pending":  Reservation.objects.filter(status="Pending").count(),
        })


# ── GET /api/v1/reports/usage/ ───────────────────────────────────────────────
class LabUsageReportView(APIView):
    """
    Returns approved reservations for the lab usage monitoring page.
    Used by teacher lab_usage.php.

    Query params:
      ?status=Approved   — filter by status (default: Approved)
      ?limit=50          — max results
      ?ordering=-date    — ordering
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        filter_status = request.query_params.get("status",   "Approved")
        limit         = int(request.query_params.get("limit", 50))

        queryset = Reservation.objects.select_related(
            "user", "lab"
        ).filter(status=filter_status).order_by("-date")[:limit]

        results = [
            {
                "id":           r.id,
                "student_name": r.user.name,
                "lab_name":     r.lab.lab_name if r.lab else "—",
                "date":         str(r.date),
                "time_slot":    r.time_slot,
                "status":       r.status,
            }
            for r in queryset
        ]

        return Response({"results": results})


# ── GET /api/v1/teacher/stats/ ───────────────────────────────────────────────
class TeacherStatsView(APIView):
    """
    Returns stats for the teacher dashboard.
    Used by teacher dashboard.php.

    Query params:
      ?user_id=<int>
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        user_id = request.query_params.get("user_id") or request.user.id

        pending_issues = Issue.objects.filter(
            user_id=user_id, status="Pending"
        ).count()

        return Response({"pending_issues": pending_issues})


# ── GET /api/v1/teacher/recent-reservations/ ─────────────────────────────────
class TeacherRecentReservationsView(APIView):
    """
    Returns recent lab reservations for the teacher dashboard table.

    Query params:
      ?user_id=<int>
      ?limit=5
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        limit = int(request.query_params.get("limit", 5))

        queryset = Reservation.objects.select_related(
            "user", "lab"
        ).filter(lab__isnull=False).order_by("-date")[:limit]

        results = [
            {
                "id":           r.id,
                "student_name": r.user.name,
                "lab_name":     r.lab.lab_name if r.lab else "—",
                "date":         str(r.date),
                "time_slot":    r.time_slot,
                "status":       r.status,
            }
            for r in queryset
        ]

        return Response({"results": results})