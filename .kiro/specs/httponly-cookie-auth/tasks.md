# Implementation Plan: HTTP-Only Cookie Authentication

## Overview

This implementation plan converts the existing token-in-response authentication to secure HTTP-only cookie-based authentication. Tasks are ordered to build incrementally, with each step building on the previous one.

## Tasks

- [ ] 1. Create configuration and service foundation
  - [ ] 1.1 Create cookie-auth configuration file
    - Create `config/cookie-auth.php` with cookie settings
    - Define cookie_name, domain, path, secure, same_site, http_only, expiration_minutes
    - All settings should read from environment variables with sensible defaults
    - _Requirements: 8.1, 8.2, 8.3_

  - [ ] 1.2 Create CookieAuthService interface and implementation
    - Create `app/Services/Contracts/CookieAuthServiceInterface.php`
    - Create `app/Services/CookieAuthService.php` implementing the interface
    - Implement `createAuthCookie(string $token, ?int $expirationMinutes = null): Cookie`
    - Implement `createExpiredAuthCookie(): Cookie`
    - Implement `getCookieConfig(): array`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [ ] 1.3 Register CookieAuthService in AppServiceProvider
    - Bind interface to implementation in service container
    - _Requirements: 1.1_

  - [ ]* 1.4 Write unit tests for CookieAuthService
    - Test cookie creation with all security attributes
    - Test expired cookie creation
    - Test configuration reading
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 2. Create middleware for token extraction
  - [ ] 2.1 Create ExtractTokenFromCookie middleware
    - Create `app/Http/Middleware/ExtractTokenFromCookie.php`
    - Extract token from auth cookie if present
    - Set Authorization header with Bearer token for Sanctum
    - Skip if Bearer token already present in request
    - _Requirements: 5.1, 5.2_

  - [ ] 2.2 Register middleware in HTTP Kernel
    - Add middleware to api middleware group
    - Ensure it runs before Sanctum authentication
    - _Requirements: 5.1, 5.2_

  - [ ]* 2.3 Write unit tests for ExtractTokenFromCookie middleware
    - Test token extraction from cookie
    - Test header setting when cookie present
    - Test no modification when bearer token exists
    - _Requirements: 5.1, 5.2_

- [ ] 3. Checkpoint - Verify foundation
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 4. Modify AuthController for cookie-based responses
  - [ ] 4.1 Update login method to use cookie response
    - Inject CookieAuthService into controller
    - Create auth cookie with token after successful login
    - Attach cookie to response using withCookie()
    - Remove token from JSON response body
    - _Requirements: 2.1, 2.2, 2.4_

  - [ ] 4.2 Update register method to use cookie response
    - Create auth cookie with token after successful registration
    - Attach cookie to response using withCookie()
    - Remove token from JSON response body
    - _Requirements: 3.1, 3.2_

  - [ ] 4.3 Update logout method to clear cookie
    - Delete current access token from database
    - Create expired cookie to clear authentication
    - Attach expired cookie to response
    - _Requirements: 4.1, 4.2, 4.3_

  - [ ] 4.4 Add token refresh endpoint
    - Create new `refresh()` method in AuthController
    - Revoke current token
    - Create new token
    - Set new auth cookie
    - Return success response
    - _Requirements: 7.1, 7.2_

  - [ ]* 4.5 Write property test for token in cookie not in response
    - **Property 2: Token in Cookie, Not in Response Body**
    - **Validates: Requirements 2.1, 2.2, 2.4, 3.1, 3.2**

  - [ ]* 4.6 Write property test for failed auth sets no cookies
    - **Property 3: Failed Authentication Sets No Cookies**
    - **Validates: Requirements 2.3, 3.3**

- [ ] 5. Configure CSRF protection
  - [ ] 5.1 Add CSRF cookie endpoint route
    - Add route for `/api/csrf-cookie` using Sanctum's csrf-cookie
    - Ensure route is accessible without authentication
    - _Requirements: 6.1_

  - [ ] 5.2 Update Sanctum stateful domains configuration
    - Update `.env` with SANCTUM_STATEFUL_DOMAINS
    - Include localhost, development, and production domains
    - _Requirements: 6.4_

  - [ ] 5.3 Configure CORS for credentials
    - Update `config/cors.php` to support credentials
    - Set `supports_credentials` to true
    - _Requirements: 5.1, 6.1_

  - [ ]* 5.4 Write property test for CSRF validation
    - **Property 7: CSRF Token Validation**
    - **Validates: Requirements 6.2, 6.3**

- [ ] 6. Checkpoint - Verify authentication flow
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Add API routes for new endpoints
  - [ ] 7.1 Add refresh token route
    - Add POST `/api/auth/refresh` route
    - Protect with auth:sanctum middleware
    - _Requirements: 7.1, 7.2_

  - [ ]* 7.2 Write property test for cookie security attributes
    - **Property 1: Cookie Security Attributes**
    - **Validates: Requirements 1.1, 1.2, 1.3, 1.5**

  - [ ]* 7.3 Write property test for logout clears cookie and token
    - **Property 4: Logout Clears Cookie and Token**
    - **Validates: Requirements 4.1, 4.2**

- [ ] 8. Write integration tests
  - [ ]* 8.1 Write property test for cookie-based authentication round trip
    - **Property 5: Cookie-Based Authentication Round Trip**
    - **Validates: Requirements 5.1, 5.2**

  - [ ]* 8.2 Write property test for missing cookie returns 401
    - **Property 6: Missing Cookie Returns 401**
    - **Validates: Requirements 5.3**

  - [ ]* 8.3 Write property test for token refresh updates cookie
    - **Property 8: Token Refresh Updates Cookie**
    - **Validates: Requirements 7.1, 7.2**

  - [ ]* 8.4 Write property test for invalid token refresh returns 401
    - **Property 9: Invalid Token Refresh Returns 401**
    - **Validates: Requirements 7.3**

- [ ] 9. Update environment configuration
  - [ ] 9.1 Add cookie auth environment variables to .env.example
    - Add AUTH_COOKIE_NAME, AUTH_COOKIE_DOMAIN, AUTH_COOKIE_PATH
    - Add AUTH_COOKIE_SECURE, AUTH_COOKIE_SAME_SITE, AUTH_COOKIE_EXPIRATION
    - Add documentation comments
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

- [ ] 10. Final checkpoint - Complete verification
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- The implementation maintains backward compatibility with existing Sanctum token authentication
