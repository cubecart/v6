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
| PHP Code | The code to execute, entered via the ACE code editor. |

## Import Snippets

Import code snippets from an XML file. This allows sharing snippets between installations.

## Hook Configuration

When viewing a plugin's hooks, each hook shows its name, trigger point, priority, and status. You can edit individual hooks to change their trigger, priority, or file path.

Use **Revert to Default** to reset a plugin's hook configuration to its original state.

> [!NOTE]
> Code snippets execute PHP directly. Incorrect code can break your store. Always test snippets on a development site first.
