# apps/reservations/urls.py

from django.urls import path
from apps.reservations.views import (
    ReservationListView,
    MyReservationsView,
    ReservationDetailView,
    PendingApprovalsView,
    ApprovalActionView,
    AdminStatsView,
    StudentDashboardView,
    ReservationHistoryView,
)

urlpatterns = [
    # GET    /api/v1/reservations/           ← all reservations (admin)
    # POST   /api/v1/reservations/           ← create reservation (student)
    path("",                ReservationListView.as_view(),   name="reservation-list"),

    # GET    /api/v1/reservations/my/        ← student's own reservations
    path("my/",             MyReservationsView.as_view(),    name="my-reservations"),

    # GET    /api/v1/reservations/<id>/      ← single reservation
    path("<int:pk>/",       ReservationDetailView.as_view(), name="reservation-detail"),

    # GET    /api/v1/reservations/history/   ← full history (admin) or by user_id
    path("history/",        ReservationHistoryView.as_view(), name="reservation-history"),
]

# These are mounted at /api/v1/ in root urls.py — admin routes use a separate prefix
# But for simplicity they're added here and included via the reservations prefix
# Root urls.py maps: path("api/v1/admin/approvals/", include("apps.reservations.admin_urls"))
# We keep them in a separate list for clarity:
admin_urlpatterns = [
    # GET    /api/v1/admin/approvals/        ← pending list (admin approvals.php)
    path("",              PendingApprovalsView.as_view(),  name="approvals-list"),

    # POST   /api/v1/admin/approvals/<id>/   ← approve or reject
    path("<int:pk>/",     ApprovalActionView.as_view(),    name="approval-action"),

    # GET    /api/v1/admin/stats/            ← dashboard counts
    path("stats/",        AdminStatsView.as_view(),        name="admin-stats"),
]