# Hooks & Code Snippets

Extend CubeCart functionality with hooks and custom PHP code snippets.

## Installed Plugins

Lists all installed plugins that use the hook system. Click a plugin name to view and configure its individual hooks.

## Code Snippets

Code snippets are custom PHP code blocks that execute at specific hook trigger points. They allow you to modify store behaviour without editing core files.

### Snippet Fields

| Field | Description |
| --- | --- |
| Status | Enable or disable the snippet. |
| Unique ID | A unique identifier for the snippet. |
| Priority | Execution order when multiple snippets use the same trigger. Lower numbers run first. |
| Description | A label describing what the snippet does. |
| Trigger | The hook event that triggers this code (e.g. class.cart.construct, class.cubecart.construct). |
| Version | Optional version number for your reference. |
| Author | Optional author name. |
| PHP Code | The code to execute, entered via the code editor. |

## Import Snippets

Import code snippets from an XML file. This allows sharing snippets between installations.

## Hook Configuration

When viewing a plugin's hooks, each hook shows its name, trigger point, priority, and status. You can edit individual hooks to change their trigger, priority, or file path.

Use **Revert to Default** to reset a plugin's hook configuration to its original state.

## Hook Code Editor

When editing an individual hook, a **Hook Code** tab provides an integrated code editor for viewing and editing the hook's PHP file directly in the browser.

- If the hook file is writable, changes can be saved using **Save** or **Save & Continue** (which reloads the current tab).
- If the hook file is read-only, the editor opens in read-only mode.

### Backups

A backup system provides basic version control for hook files:

- **Automatic backups** — A timestamped backup is created before each save or restore, but only if the content has actually changed.
- **Default version** — The first time a hook file is viewed, a default backup is saved automatically, tagged with the plugin's version number (e.g. `v1.0.0`). When the plugin updates to a new version, a new default is created and older version defaults are removed.
- **Maximum 10 backups** — Only the 10 most recent timestamped backups are kept. When the limit is reached, the oldest backup is automatically deleted.
- **Restore** — Any backup (including the default) can be restored. The current file is backed up before restoring.
- **Delete** — Timestamped backups can be deleted individually. The default version backup cannot be deleted.

> [!NOTE]
> Code snippets and hook files execute PHP directly. Incorrect code can break your store. Always test changes on a development site first.
