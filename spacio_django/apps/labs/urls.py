# apps/labs/urls.py

from django.urls import path
from apps.labs.views import LabListView, LabDetailView, EquipmentListView, EquipmentDetailView

urlpatterns = [
    # GET    /api/v1/labs/                  ← lab dropdown list
    path("",                LabListView.as_view(),         name="lab-list"),

    # GET    /api/v1/labs/<id>/             ← single lab detail
    path("<int:pk>/",       LabDetailView.as_view(),        name="lab-detail"),

    # GET    /api/v1/labs/equipment/        ← paginated equipment list
    # POST   /api/v1/labs/equipment/        ← add new equipment
    path("equipment/",      EquipmentListView.as_view(),    name="equipment-list"),

    # PUT    /api/v1/labs/equipment/<id>/   ← update / soft-delete equipment
    path("equipment/<int:pk>/", EquipmentDetailView.as_view(), name="equipment-detail"),
]