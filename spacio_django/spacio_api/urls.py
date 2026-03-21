"""
spacio_api/urls.py
Root URL configuration — all API routes live under /api/v1/
"""

from django.contrib import admin
from django.urls    import path, include
from drf_spectacular.views import SpectacularAPIView, SpectacularSwaggerView

from apps.reservations.views import AdminStatsView
from apps.reservations.urls  import admin_urlpatterns
from apps.reports.urls       import teacher_urlpatterns

urlpatterns = [

    # Django admin panel
    path("admin/", admin.site.urls),

    # ── Authentication ─────────────────────────────────────────────────────────
    path("api/v1/auth/",         include("apps.authentication.urls")),

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

    # GET/POST /api/v1/admin/inventory/       reuse labs equipment endpoints
    path("api/v1/admin/inventory/",  include("apps.labs.urls")),

    # ── Teacher routes ─────────────────────────────────────────────────────────
    # GET /api/v1/teacher/stats/
    # GET /api/v1/teacher/recent-reservations/
    path("api/v1/teacher/",          include((teacher_urlpatterns, "teacher"))),

    # ── API documentation ──────────────────────────────────────────────────────
    path("api/schema/", SpectacularAPIView.as_view(),                           name="schema"),
    path("api/docs/",   SpectacularSwaggerView.as_view(url_name="schema"),      name="swagger-ui"),
]