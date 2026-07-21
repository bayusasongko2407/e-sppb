# 03_API_ANALYSIS.md — E-SPPB Enterprise API Analysis

> **Audit Type:** API Endpoints & Interfaces Analysis (Read-Only)  
> **Project:** E-SPPB Enterprise  
> **Date:** 2026-07-16  

---

## Executive Summary

There is no dedicated REST API codebase (e.g. no `routes/api.php` or API Controllers). The project exposes operations via Filament Resources (which rely on dynamic Livewire AJAX updates) and two custom web routes.

---

## Endpoint Inventory

Only two routes are explicitly registered in `routes/web.php`:

### 1. Document Verification Route
- **URI:** `/verify/document/{sha256Token}`
- **HTTP Method:** `GET`
- **Controller:** `App\Http\Controllers\DocumentVerificationController@verifyPublicPage`
- **Middleware:** `throttle:60,1` (Throttle requests to 60 per minute per IP address)
- **Parameter Validation:** The `sha256Token` is verified using regex: `[a-f0-9]{64}` (exactly 64 hex characters representing a SHA256 string).
- **Access Control:** Fully public. Authentication is not required.

### 2. SPPB Print Preview Route
- **URI:** `/sppb/{id}/preview`
- **HTTP Method:** `GET`
- **Controller:** `App\Http\Controllers\SppbPreviewController@preview`
- **Middleware:** `auth` (Requires user to be logged in)
- **Access Control:** Authorization is verified via policy check: `Gate::authorize('view', $sppbHeader)`.

---

## Request & Response Patterns

### Document Verification Page
- **Content Negotiation:** If the request specifies `Accept: application/json` or is an AJAX call (`wantsJson()`), the controller returns a JSON representation of the verification payload. Otherwise, it renders the HTML verification layout page.
- **Status Codes:**
  - `200 OK`: Valid document signature.
  - `403 Forbidden`: Superseded, expired, or revoked document status.
  - `404 Not Found`: Invalid SHA256 verification token.

### SPPB Print Preview
- **Behavior:** Dynamically generates a PDF document template. Once generated successfully (status is marked `READY`), it loads an optimized HTML template designed to trigger the browser's print-to-PDF engine.
- **Status Codes:**
  - `200 OK`: Layout loaded successfully.
  - `403 Forbidden`: User fails SPPB ownership/plant view permissions.
  - `404 Not Found`: Invalid SPPB header database record.
  - `500 Internal Server Error`: Generated output status fails to mark `READY`.

---

## Missing API & Future Gaps

- **No Mobile API Support:** The project lacks endpoints for a Flutter/mobile client. Mobilizing workflows would require implementing a complete authentication mechanism (e.g. Laravel Laravel Sanctum) and creating API controllers.
- **No Third-Party Integrations:** There are no secure webhook endpoints or token-based endpoints for communicating with external ERP systems.
- **Filament Livewire Security:** Standard Filament actions handle mutations. This is highly secure but acts as a closed-source interface that cannot be directly integrated into other software applications.
