# WordPress Facilitator Slides Creation — Plugin Guide

This plugin adds a workshop slide creation assistant to the Cowork tab in the Claude app. Once installed, it walks you through a step-by-step process to turn your course content and facilitation guide into a complete, branded PowerPoint slide deck.

---

## Requirements

- The Claude app with the Cowork tab
- A paid Claude plan (Pro, Team, or Max)
- Your course materials ready to go (see "What you'll need" below)

---

## How to Install

**Option A — Double-click to install (easiest):**
1. Double-click the `wordpress-facilitator-slides-creation.plugin` file
2. When prompted to choose an app, select **Claude**
3. Claude will open a new task window with the file ready to go — press **Enter** to confirm installation

**Option B — Attach from within the app:**
1. Open the Claude app and go to the **Cowork** tab
2. Start a new task
3. Attach the `.plugin` file to the chat using the attachment/paperclip icon
4. Claude will analyze the file and ask if you'd like to install it — confirm to complete installation

> **Note:** You may also see a path via **Settings → Customize → Personal Plugins → Upload**, but this currently returns an upload error. Use either option above instead.

To confirm it's installed, start a new task and say "create workshop slides." The assistant should respond with the connection check and pre-flight checklist.

---

## What You'll Need

Have these ready before you start:

**Course content** — a document (Word, PDF, or text) containing the learning content: topics, lessons, key concepts, and learning objectives.

**Facilitation guide** — a document with session timing, activity instructions, transition language, and talking points for the facilitator.

**Audience & goals** — a few sentences describing who the participants are, what they should be able to do by the end, and any relevant context (skill level, role, institution type, etc.).

---

## How to Use It

1. Open a new Cowork session and select the folder where your materials are saved
2. Say any of the following to get started:
   - "Create workshop slides"
   - "Build a slide deck from my course content"
   - "Generate slides from my facilitation guide"
   - "Turn this curriculum into slides"
3. The assistant will run a **connection check** — confirm your setup or dismiss it if you're already connected
4. Work through each step with the assistant:
   - Confirm your inputs are ready
   - Review the brand it will apply
   - Approve the slide-by-slide outline before anything is generated
   - The assistant builds the full deck and runs a quality check
5. When complete, a link to the finished `.pptx` file will appear in the chat

---

## Optional: Connect Figma

If your team uses the WordPress Figma design system, connecting the Figma MCP lets the assistant pull live brand colors and fonts instead of using hardcoded defaults. The connection check at the start of each session will guide you through this if needed.

To connect Figma:
1. Go to **Settings → Connections** in the Claude app
2. Find Figma and click **Connect**
3. Sign in with the account that has access to the WordPress design system

---

## What the Plugin Produces

A `.pptx` slide deck structured for WordPress Facilitator Training Program workshops, including:

- Title slide and "How to Use This Deck" orientation slide
- Day divider slides for each workshop day
- Session header slides with learning objectives
- Content slides (2–5 per session)
- Activity slides with step-by-step instructions
- Break and reflection slides
- Speaker notes on every slide

The output follows WordPress brand guidelines and the writing style of the WordPress Facilitator Training Program.

**Example output:** [Leading WordPress Education Programs — Facilitator Slides](https://docs.google.com/presentation/d/1stzbHxNoW5gxaZdfa1dwCvWENm1geuNH/edit?usp=sharing&ouid=105593293784291115388&rtpof=true&sd=true)
Generated from the [facilitation guide](https://docs.google.com/document/d/15zlJTDc8qONihe495OuAga-UlRY8Ylzxu0gM1PHLtI8/edit?tab=t.0) and the [Leading WordPress Education Programs course](https://learn.wordpress.org/course/leading-wordpress-education-programs/).
