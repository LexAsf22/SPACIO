# apps/labs/views.py

from rest_framework          import status
from rest_framework.views    import APIView
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated

from apps.labs.models       import Laboratory, Equipment
from apps.labs.serializers  import LaboratorySerializer, EquipmentSerializer


# ── GET /api/v1/labs/ ─────────────────────────────────────────────────────────
class LabListView(APIView):
    """Returns all labs — used to populate dropdowns in PHP forms."""
    permission_classes = [IsAuthenticated]

    def get(self, request):
        labs = Laboratory.objects.all().order_by("campus", "lab_name")
        return Response(LaboratorySerializer(labs, many=True).data)


# ── GET /api/v1/labs/<id>/ ────────────────────────────────────────────────────
class LabDetailView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request, pk):
        try:
            lab = Laboratory.objects.get(pk=pk)
        except Laboratory.DoesNotExist:
            return Response({"detail": "Lab not found."}, status=status.HTTP_404_NOT_FOUND)
        return Response(LaboratorySerializer(lab).data)


# ── GET /api/v1/labs/equipment/ ───────────────────────────────────────────────
class EquipmentListView(APIView):
    """
    Returns equipment list.
    Supports ?lab_filter=<id> and ?search=<term> query params.
    Supports pagination via ?page=<n>&per_page=<n>.
    """
    permission_classes = [IsAuthenticated]

    def get(self, request):
        queryset   = Equipment.objects.select_related("lab").all()
        search     = request.query_params.get("search",     "")
        lab_filter = request.query_params.get("lab_filter", 0)
        per_page   = int(request.query_params.get("per_page", 10))
        page       = int(request.query_params.get("page",     1))

        if search:
            queryset = queryset.filter(equipment_name__icontains=search)
        if lab_filter:
            queryset = queryset.filter(lab_id=lab_filter)

        queryset   = queryset.order_by("lab__lab_name", "equipment_name")
        total      = queryset.count()
        offset     = (page - 1) * per_page
        results    = queryset[offset : offset + per_page]

        return Response({
            "count":       total,
            "total_pages": max(1, -(-total // per_page)),  # ceiling division
            "results":     EquipmentSerializer(results, many=True).data,
        })

    def post(self, request):
        """Add new equipment — called by admin inventory.php."""
        serializer = EquipmentSerializer(data=request.data)
        if serializer.is_valid():
            serializer.save()
            return Response(serializer.data, status=status.HTTP_201_CREATED)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


# ── PUT /api/v1/labs/equipment/<id>/ ─────────────────────────────────────────
class EquipmentDetailView(APIView):
    permission_classes = [IsAuthenticated]

    def get_object(self, pk):
        try:
            return Equipment.objects.select_related("lab").get(pk=pk)
        except Equipment.DoesNotExist:
            return None

    def put(self, request, pk):
        """Update equipment — used for soft-delete flag from PHP."""
        equipment = self.get_object(pk)
        if not equipment:
            return Response({"detail": "Equipment not found."}, status=status.HTTP_404_NOT_FOUND)

        # Handle soft-delete from PHP
        if request.data.get("deleted"):
            equipment.delete()
            return Response({"message": "Equipment deleted."}, status=status.HTTP_200_OK)

        serializer = EquipmentSerializer(equipment, data=request.data, partial=True)
        if serializer.is_valid():
            serializer.save()
            return Response(serializer.data)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)