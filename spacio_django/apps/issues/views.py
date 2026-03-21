# apps/issues/views.py

from rest_framework          import status
from rest_framework.views    import APIView
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated

from apps.issues.models      import Issue
from apps.issues.serializers import IssueSerializer, IssueCreateSerializer


# ── GET + POST /api/v1/issues/ ────────────────────────────────────────────────
class IssueListView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request):
        """
        List issues.
        ?user_id=<int>   — filter by teacher (issue_status.php)
        ?ordering=-created_at — latest first
        """
        queryset = Issue.objects.select_related("user").all()

        user_id = request.query_params.get("user_id")
        if user_id:
            queryset = queryset.filter(user_id=int(user_id))

        return Response(IssueSerializer(queryset, many=True).data)

    def post(self, request):
        """Create a new issue — called by teacher report_issue.php."""
        serializer = IssueCreateSerializer(data=request.data)
        if serializer.is_valid():
            issue = serializer.save()
            return Response(
                IssueSerializer(issue).data,
                status=status.HTTP_201_CREATED,
            )
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


# ── POST /api/v1/issues/<id>/status/ ─────────────────────────────────────────
class IssueStatusView(APIView):
    """Update issue status — called by admin maintenance.php."""
    permission_classes = [IsAuthenticated]

    ALLOWED_STATUSES = ["In Progress", "Done"]

    def post(self, request, pk):
        new_status = request.data.get("status", "")

        if new_status not in self.ALLOWED_STATUSES:
            return Response(
                {"detail": f"Status must be one of: {', '.join(self.ALLOWED_STATUSES)}."},
                status=status.HTTP_400_BAD_REQUEST,
            )

        try:
            issue = Issue.objects.get(pk=pk)
        except Issue.DoesNotExist:
            return Response({"detail": "Issue not found."}, status=status.HTTP_404_NOT_FOUND)

        issue.status = new_status
        issue.save()

        return Response({
            "message": f"Issue marked as '{new_status}'.",
            "id":      issue.id,
            "status":  issue.status,
        })