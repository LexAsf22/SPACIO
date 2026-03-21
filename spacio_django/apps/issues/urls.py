# apps/issues/urls.py

from django.urls import path
from apps.issues.views import IssueListView, IssueStatusView

urlpatterns = [
    # GET    /api/v1/issues/              ← list (filter by ?user_id= for teacher)
    # POST   /api/v1/issues/              ← create (report_issue.php)
    path("",             IssueListView.as_view(),   name="issue-list"),

    # POST   /api/v1/issues/<id>/status/  ← update status (maintenance.php)
    path("<int:pk>/status/", IssueStatusView.as_view(), name="issue-status"),
]