# Level 5 Deal Room for BuddyBoss

This module adds a secure Deal Room feature to BuddyBoss Platform for Level 5 investors and partners.

## Features

### Deal Room
- Secure document repository for investors
- Document categories:
  - Pitch Decks
  - Market Research
  - Competitive Analysis
  - Business Plans
  - Financial Projections
  - Cap Table
  - Legal Documents
- Access control (only investors and admins)
- Document upload and management
- Organized by Level 5 sections

### Level 5 Groups
The module creates the following private groups:
1. **SudoSelf** - Self-improvement and personal development
2. **BizBox** - Business tools and resources
3. **Level 5 Podcast** - Podcast discussions and content
4. **Sausage Software** - Software development and tech
5. **Trillion Club** - Exclusive high-growth entrepreneurs

## Installation

1. The Deal Room module is already included in the `/src/bp-deal-room/` directory
2. To activate and set up the module, visit: `your-site.com/wp-admin/?bp_deal_room_activate=1`
3. This will:
   - Create the necessary database tables
   - Add the "Investor" user role
   - Create the five Level 5 groups

## Access Control

### Who can access the Deal Room?
- Site administrators
- Users with the "investor" role
- Users manually added to the access list

### How to grant access:
1. **Via User Role**: Change a user's role to "Investor" in WordPress admin
2. **Via Code**: Use the filter `bp_deal_room_user_has_access`

## Usage

### For Users
1. Navigate to "Deal Room" in your profile menu (only visible if you have access)
2. Browse documents by category
3. Upload new documents using the upload buttons
4. Download documents by clicking the download button

### For Administrators
1. Manage investor access through WordPress user roles
2. All uploaded documents are stored in the WordPress uploads directory
3. Database tables store document metadata and access permissions

## Developer Notes

### File Structure
```
bp-deal-room/
├── bp-deal-room-loader.php      # Component loader
├── bp-deal-room-functions.php   # Core functions
├── bp-deal-room-template.php    # Template functions
├── bp-deal-room-filters.php     # Hooks and filters
├── bp-deal-room-settings.php    # Settings (placeholder)
├── bp-deal-room-activation.php  # Activation script
├── classes/
│   └── class-bp-deal-room-component.php  # Main component class
└── README.md                    # This file
```

### Database Tables
- `bp_deal_room` - Main deal room table
- `bp_deal_room_documents` - Document storage
- `bp_deal_room_access` - Access control

### Hooks & Filters
- `bp_deal_room_user_has_access` - Filter to control user access
- `bp_deal_room_document_types` - Filter to modify document types
- `bp_deal_room_level5_sections` - Filter to modify Level 5 sections

## Security

- All file uploads are handled through WordPress media functions
- Access is restricted at the component level
- AJAX endpoints check nonces and user permissions
- Documents can only be deleted by admins or the uploader

## Customization

To add more document types:
```php
add_filter( 'bp_deal_room_document_types', function( $types ) {
    $types['new_type'] = __( 'New Document Type', 'buddyboss' );
    return $types;
} );
```

To grant access to specific users:
```php
add_filter( 'bp_deal_room_user_has_access', function( $has_access, $user_id ) {
    // Your custom logic here
    return $has_access;
}, 10, 2 );
```