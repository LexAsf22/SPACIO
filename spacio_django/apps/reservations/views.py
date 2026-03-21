# apps/reservations/views.py

from rest_framework          import status
from rest_framework.views    import APIView
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated

from apps.reservations.models      import Reservation
from apps.reservations.serializers import ReservationSerializer, ReservationCreateSerializer


# ── GET + POST /api/v1/reservations/ ─────────────────────────────────────────
class ReservationListView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request):
        """List all reservations — admin use."""
        reservations = Reservation.objects.select_related(
            "user", "lab", "equipment"
        ).all()
        return Response(ReservationSerializer(reservations, many=True).data)

    def post(self, request):
        """Create a new reservation — called by reserve_lab.php / reserve_equipment.php."""
        serializer = ReservationCreateSerializer(data=request.data)
        if serializer.is_valid():
            reservation = serializer.save()
            return Response(
                ReservationSerializer(reservation).data,
                status=status.HTTP_201_CREATED,
            )
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


# ── GET /api/v1/reservations/my/ ─────────────────────────────────────────────
class MyReservationsView(APIView):
    """Returns reservations belonging to the requesting user (student)."""
    permission_classes = [IsAuthenticated]

    def get(self, request):
        user_id = request.query_params.get("user_id") or request.user.id
        reservations = Reservation.objects.select_related(
            "user", "lab", "equipment"
        ).filter(user_id=user_id).order_by("-date")

        return Response(ReservationSerializer(reservations, many=True).data)


# ── GET /api/v1/reservations/<id>/ ───────────────────────────────────────────
class ReservationDetailView(APIView):
    permission_classes = [IsAuthenticated]

    def get_object(self, pk):
        try:
            return Reservation.objects.select_related(
                "user", "lab", "equipment"
            ).get(pk=pk)
        except Reservation.DoesNotExist:
            return None

    def get(self, request, pk):
        reservation = self.get_object(pk)
        if not reservation:
            return Response({"detail": "Not found."}, status=status.HTTP_404_NOT_FOUND)
        return Response(ReservationSerializer(reservation).data)


# ── GET /api/v1/admin/approvals/ ─────────────────────────────────────────────
class PendingApprovalsView(APIView):
    """Returns all pending reservations — for admin approvals.php."""
    permission_classes = [IsAuthenticated]

    def get(self, request):
        pending = Reservation.objects.select_related(
            "user", "lab", "equipment"
        ).filter(status="Pending").order_by("date", "time_slot")

        return Response(ReservationSerializer(pending, many=True).data)


# ── POST /api/v1/admin/approvals/<id>/ ───────────────────────────────────────
class ApprovalActionView(APIView):
    """Approve or reject a reservation — called by admin approvals.php."""
    permission_classes = [IsAuthenticated]

    def post(self, request, pk):
        action = request.data.get("action", "").lower()

        if action not in ("approve", "reject"):
            return Response(
                {"detail": "Action must be 'approve' or 'reject'."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        try:
            reservation = Reservation.objects.get(pk=pk)
        except Reservation.DoesNotExist:
            return Response({"detail": "Reservation not found."}, status=status.HTTP_404_NOT_FOUND)

        reservation.status = "Approved" if action == "approve" else "Rejected"
        reservation.save()

        return Response({
            "message": f"Reservation {reservation.status.lower()} successfully.",
            "id":      reservation.id,
            "status":  reservation.status,
        })


# ── GET /api/v1/admin/stats/ ──────────────────────────────────────────────────
class AdminStatsView(APIView):
    """Dashboard counts — total reservations, pending, total issues."""
    permission_classes = [IsAuthenticated]

    def get(self, request):
        from apps.issues.models import Issue

        return Response({
            "total_reservations": Reservation.objects.count(),
            "total_pending":      Reservation.objects.filter(status="Pending").count(),
            "total_issues":       Issue.objects.count(),
        })