# apps/reports/urls.py

from django.urls import path
from apps.reports.views import (
    ReservationReportView,
    LabUsageReportView,
    TeacherStatsView,
    TeacherRecentReservationsView,
)

urlpatterns = [
    # GET /api/v1/reports/reservations/           ← bar chart data (admin reports.php)
    path("reservations/", ReservationReportView.as_view(),           name="report-reservations"),

    # GET /api/v1/reports/usage/                  ← lab usage table (teacher lab_usage.php)
    path("usage/",        LabUsageReportView.as_view(),              name="report-usage"),
]

# Teacher-specific routes — mounted via root urls.py at /api/v1/teacher/
teacher_urlpatterns = [
    # GET /api/v1/teacher/stats/
    path("stats/",               TeacherStatsView.as_view(),                name="teacher-stats"),

    # GET /api/v1/teacher/recent-reservations/
    path("recent-reservations/", TeacherRecentReservationsView.as_view(),   name="teacher-recent"),
]