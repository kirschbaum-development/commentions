# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package Overview

Commentions is a Laravel package for Filament that adds commenting functionality to resources. It supports:
- Adding comments to any model
- Mentioning users in comments
- Comment reactions
- Comment permissions
- Event-driven notifications for mentions and reactions
- Polling for new comments

## Development Commands

### PHP Development

```bash
# Install PHP dependencies
composer install

# Run tests
./vendor/bin/pest

# Code style checks
composer lint:check

# Apply code style fixes
composer lint

# Static analysis
composer static-analysis
```

### Frontend Development

```bash
# Install JS dependencies
npm install

# Build frontend assets for development with watch mode
npm run dev

# Build frontend assets for production
npm run build

# Individual build commands
npm run dev:scripts     # Build JS in dev mode with watch
npm run build:scripts   # Build JS for production
npm run dev:styles      # Build CSS in dev mode with watch
npm run build:styles    # Build CSS for production
```

## Architecture

### Core Components

1. **Models**
   - `Comment` - The main comment model
   - `CommentReaction` - Model for storing reactions to comments
   - `RenderableComment` - Data object for non-comment items to display in comment lists

2. **Contracts**
   - `Commentable` - Interface for models that can receive comments
   - `Commenter` - Interface for user models that can create comments
   - `RenderableComment` - Interface for objects that can be rendered as comments

3. **Traits**
   - `HasComments` - For models that implement `Commentable`
   - `HasMentions` - For Livewire components that support user mentions
   - `HasPolling` - For Livewire components that support polling for new comments

4. **Livewire Components**
   - `Comment` - Renders a single comment
   - `CommentList` - Renders a list of comments
   - `Comments` - Main component for the comment system
   - `Reactions` - Component for reactions on comments

5. **Filament Integration**
   - `CommentionsPlugin` - Filament plugin for registering the package
   - `CommentsEntry` - Infolist component for displaying comments
   - `CommentsAction` - Action for displaying comments in Filament
   - `CommentsTableAction` - Table action for comments

6. **Events**
   - `UserWasMentionedEvent` - Dispatched when a user is mentioned in a comment
   - `CommentWasReactedEvent` - Dispatched when a user reacts to a comment

### Extending and Customizing

The package is designed to be extensible. Key customization points:
- Override the Comment model
- Implement a custom CommentPolicy
- Register event listeners for mentions and reactions
- Customize the Commenter identification using `getCommenterName()`

## Testing

Tests use Pest PHP with Laravel and Livewire testing plugins. The testing setup:
- Uses SQLite in-memory database
- Creates test tables including comments, reactions, users, and posts
- Tests cover comment creation, editing, reactions, and permissions

When creating new features, ensure:
1. Write Pest tests that cover the new functionality
2. Test the feature with Filament integration
3. Check stylistic consistency with existing code
