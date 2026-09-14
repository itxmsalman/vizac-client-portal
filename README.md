# VIZAC Client Portal

VIZAC Client Portal is a WordPress-based consultancy client management plugin designed to help clients track the progress of their visa or application case through an authenticated portal.

## Key Features

- User-specific application progress tracking
- Application stages:
  - Application Submitted
  - Under Review
  - Visa Approved
  - Visa Issued
  - Visa Refused
- Separate approval and refusal workflows
- Visual progress bar
- Client guidance messages
- Application status history
- Timestamped audit trail
- Displays who updated the application status
- WordPress shortcode integration
- Advanced Custom Fields (ACF) integration
- WordPress user metadata integration

## Shortcodes

```text
[application_progress_bar]
[display_application_progress_bar]

Technology Stack
WordPress
PHP
Advanced Custom Fields (ACF)
WordPress User Meta
WordPress Shortcodes
WordPress Hooks and Filters
Elementor
BookingPress
Project Purpose

The system was developed to address a common communication problem in consultancy services: clients frequently need to contact consultants to ask for updates on their application progress.

VIZAC provides clients with an authenticated portal where they can view the latest status maintained by the consultancy team.


Application Workflow

Application Submitted
        |
        v
   Under Review
      /     \
     v       v
Approved   Refused
   |
   v
Visa Issued


Status History

Each application status change records:

Previous status
New status
Date and time
WordPress user who made the update

This provides an audit trail of the client application lifecycle.

Academic Context

This project was developed as part of MSc Computer Science work and demonstrates practical experience in:

Web application development
Authentication
User-specific workflows
Auditability
Application state management
Data governance
Human-centred system design
WordPress plugin development
Current Version

v1.0.0

Author

Muhammad Salman
