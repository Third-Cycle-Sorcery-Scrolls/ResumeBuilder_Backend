# TODO Lists

- Templates :- Add a way to add templates by the admin(in any style preferences)
- Logs:- classify logs as user activity log, system logs, error logs, and security logs.
- make settings in admin page functional
- download resume as pdf

# Resume Builder For Admin Plans

- mini canva for resumes.

1. What admin builder should actually do? they do 3 controlled things.

- Define structure:
  what sections exist (Experience, Skills, etc.)
  order of sections
  layout (single column, two column, sidebar)
- Define layout rules.
  width split (70/30,50/50)
  spacing density (compact / normal / spacious)
  alignment rules
- Define styling theme
  fonts
  colors
  section separators
  header style

- core ui layout of the admin builder.
  Think of a 3-panel system:

(A) Left Panel → “Block Library”

This is where drag starts.

Contains reusable blocks:

1. Basic blocks
   Header (name, title)
   Summary
   Experience
   Education
   Skills
   Projects
   Certifications
   Languages
   Advanced blocks (optional later)
   Awards
   Publications
   Custom section
   Divider
   Spacer
2. Center Panel → “Canvas (Live Resume Preview)”
   This is the most important part.
   It shows:
   real-time resume layout
   drag-drop rearrangement
   section nesting (if supported)
3. Right Panel → “Inspector / Properties”

   When a section is clicked:

   For section-level settings:
   section title label
   visibility toggle
   spacing (small/medium/large)
   repeatable toggle (for experience/projects)
   For layout-level settings:
   column layout
   background color (optional)
   padding

4. Drag & Drop behavior (very important)

   You have 3 drag types:
   1. Add block from library → canvas
      dragging “Experience” adds a new section
   2. Reorder sections inside canvas
      reorder Experience above Education
   3. Nested layout drag (advanced)
      move Skills into left column of a 2-column layout

{
"layout": "two-column",
"structure": [
{ "type": "header" },
{ "type": "summary" },
{
"type": "columns",
"children": [
[{ "type": "experience" }],
[{ "type": "skills" }]
]
}
]
}

Features you SHOULD include in MVP
Must-have (MVP v1)
drag & drop sections
reorder sections
preview resume live
add/remove section
save template JSON
basic layout: 1-column + 2-column

Should-have (v2)
nested columns (drag inside columns)
spacing controls
section duplication
custom section title
template preview mode (fake data rendering)
Nice-to-have (later)
multi-page resume preview
PDF export preview inside builder
theme presets (modern, classic, minimal)
undo/redo system

Drag & Drop library choice (important for Next.js)

You have 3 realistic options:

Option A: dnd-kit (BEST)
modern
flexible nested drag
used in production apps
Option B: react-beautiful-dnd
easier but less maintained
Option C: custom drag logic
not recommended for nested layouts

👉 Recommendation: dnd-kit

UX flow (how admin uses it)
Step 1

Create template → “Blank Resume”

Step 2

Drag blocks:

Header
Summary
Experience
Step 3

Switch layout:

single column → two column
Step 4

Arrange:

Left: Experience + Education
Right: Skills + Projects
Step 5

Save template

Key design decision (this makes or breaks system)

You must decide:

❗ Fixed blocks vs Custom blocks
Fixed blocks (recommended for MVP)
Experience always behaves same way
Skills always same structure
Custom blocks (advanced)
admin defines arbitrary fields

👉 Start with FIXED blocks only.

Otherwise system becomes unmanageable.
