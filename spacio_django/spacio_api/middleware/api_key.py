"""
spacio_api/middleware/api_key.py

Validates the X-API-Key header on every incoming request.
PHP sends this key (from its .env API_SECRET_KEY) on every djangoGet/djangoPost call.
Django checks it against INTERNAL_API_KEY in settings.py.

Public endpoints (login, register, Django admin) are exempt from this check.
"""

from django.conf     import settings
from django.http     import JsonResponse


# Paths that do NOT require the API key
_EXEMPT_PATHS = (
    "/admin/",          # Django admin panel
    "/api/schema/",     # OpenAPI schema
    "/api/docs/",       # Swagger UI
)


class ApiKeyMiddleware:
    """
    Rejects any request that does not carry the correct X-API-Key header,
    unless the path is in the exempt list above.
    """

    def __init__(self, get_response):
        self.get_response  = get_response
        self.internal_key  = getattr(settings, "INTERNAL_API_KEY", "")

    def __call__(self, request):

        # Skip check if no key is configured (e.g. during initial setup / tests)
        if not self.internal_key:
            return self.get_response(request)

        # Skip check for exempt paths
        if any(request.path.startswith(p) for p in _EXEMPT_PATHS):
            return self.get_response(request)

        # Validate the header
        incoming_key = request.headers.get("X-Api-Key", "")
        if incoming_key != self.internal_key:
            return JsonResponse(
                {"detail": "Forbidden — invalid or missing API key."},
                status=403,
            )

        return self.get_response(request)