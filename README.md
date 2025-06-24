# Morpheo Dijital Website Price Calculator

A WordPress plugin for estimating website project costs and allowing visitors to book an appointment.

## Installation

1. Copy the plugin folder into your WordPress installation under `wp-content/plugins`.
2. In the admin dashboard open **Plugins** and activate **Morpheo Dijital Website Price Calculator**.
3. Activation will create the database tables used to store calculation results and appointments.

## Settings

Navigate to **Price Calculator → Settings** in the WordPress admin menu.

- **Appointment Redirect URL** – URL of the page that opens when a visitor confirms an appointment. This is usually a contact form or booking page. Update the value and save changes.

## Usage

Add the shortcode `[morpheo_web_calculator]` to any post or page where you want the calculator displayed.

### Shortcode parameters

- `theme` – `dark` (default) or `light` to set the initial theme.
- `show_appointment` – `true` (default) or `false` to hide the appointment booking step.

Example:

\`\`\`
[morpheo_web_calculator theme="light" show_appointment="false"]
\`\`\`

Save the page and visit it on the front‑end to see the calculator in action.
