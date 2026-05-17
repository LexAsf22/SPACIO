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
            d = serializer.validated_data
            from django.db import connection
            with connection.cursor() as cursor:
                cursor.execute(
                    """
                    INSERT INTO issues (user_id, campus, room, category, description, priority, status, created_at)
                    VALUES (%s, %s, %s, %s, %s, %s, 'Pending', NOW())
                    """,
                    [
                        d["user"].id,
                        d["campus"],
                        d["room"],
                        d["category"],
                        d.get("description", ""),
                        d.get("priority", "Low"),
                    ]
                )
                new_id = cursor.lastrowid
            issue = Issue.objects.select_related("user").get(pk=new_id)
            return Response(IssueSerializer(issue).data, status=status.HTTP_201_CREATED)
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