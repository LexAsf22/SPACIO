"""
spacio_api/urls.py
Root URL configuration — all API routes live under /api/v1/
"""

from django.contrib import admin
from django.urls    import path, include
from drf_spectacular.views import SpectacularAPIView, SpectacularSwaggerView
from rest_framework_simplejwt.views import TokenRefreshView

from apps.reservations.views import AdminStatsView, StudentDashboardView
from apps.reservations.urls  import admin_urlpatterns
from apps.reports.urls       import teacher_urlpatterns
from apps.labs.views import EquipmentListView, EquipmentDetailView

urlpatterns = [

    # Django admin panel
    path("admin/", admin.site.urls),

    # ── Authentication ─────────────────────────────────────────────────────────
    path("api/v1/auth/",         include("apps.authentication.urls")),
    path("api/v1/auth/refresh/", TokenRefreshView.as_view(), name="token-refresh"),

    # ── Reservations ───────────────────────────────────────────────────────────
    path("api/v1/reservations/", include("apps.reservations.urls")),

    # ── Availability ───────────────────────────────────────────────────────────
    path("api/v1/availability/", include("apps.availability.urls")),

    # ── Issues ─────────────────────────────────────────────────────────────────
    path("api/v1/issues/",       include("apps.issues.urls")),

    # ── Reports ────────────────────────────────────────────────────────────────
    path("api/v1/reports/",      include("apps.reports.urls")),

    # ── Labs & Equipment ───────────────────────────────────────────────────────
    path("api/v1/labs/",         include("apps.labs.urls")),

    # ── Admin routes ───────────────────────────────────────────────────────────
    # GET/POST /api/v1/admin/approvals/       from reservations app
    # GET/POST /api/v1/admin/approvals/<id>/  from reservations app
    path("api/v1/admin/approvals/",  include((admin_urlpatterns, "admin-approvals"))),

    # GET /api/v1/admin/stats/                dashboard counts
    path("api/v1/admin/stats/",      AdminStatsView.as_view(), name="admin-stats"),
    path("api/v1/student/dashboard/", StudentDashboardView.as_view(), name="student-dashboard"),

    # GET/POST /api/v1/admin/inventory/       reuse labs equipment endpoints
    path("api/v1/admin/inventory/",          EquipmentListView.as_view(),  name="admin-inventory-list"),
path("api/v1/admin/inventory/<int:pk>/", EquipmentDetailView.as_view(), name="admin-inventory-detail"),

    # ── Teacher routes ─────────────────────────────────────────────────────────
    # GET /api/v1/teacher/stats/
    # GET /api/v1/teacher/recent-reservations/
    path("api/v1/teacher/",          include((teacher_urlpatterns, "teacher"))),

    # ── API documentation ──────────────────────────────────────────────────────
    path("api/schema/", SpectacularAPIView.as_view(),                           name="schema"),
    path("api/docs/",   SpectacularSwaggerView.as_view(url_name="schema"),      name="swagger-ui"),
]