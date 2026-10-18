# Graph Report - hospital  (2026-10-18)

## Corpus Check
- 68 files · ~16,738 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 6 file(s) not represented in the graph (top: (none) 2, .css 2, .woff2 1)

## Summary
- 290 nodes · 425 edges · 35 communities (11 shown, 24 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 15 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `e7a98b57`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- yii\helpers\Html
- AdmissionController.php
- Patient
- Admission
- composer.json
- Doctor
- Admission-to-discharge workflow
- AdmissionService
- shamsi.js
- AdmissionSearch
- install.sql
- HospitalFormatter
- AppAsset.php
- enhance
- Yii2 Debug Toolbar Configuration
- PersianSerialColumn
- Historical Gregorian Date Input Explanation
- Yii Software License
- Font Redistribution Conditions
- close
- JalaliDate

## God Nodes (most connected - your core abstractions)
1. `Admission` - 26 edges
2. `Patient` - 24 edges
3. `Discharge` - 16 edges
4. `render()` - 15 edges
5. `FormInput` - 14 edges
6. `Doctor` - 12 edges
7. `AdmissionService` - 11 edges
8. `Ward` - 11 edges
9. `enhance()` - 11 edges
10. `ServiceAmount` - 10 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --references--> `Ward`  [EXTRACTED]
  app/views/admission/_form.php → app/models/Ward.php

## Import Cycles
- None detected.

## Communities (35 total, 24 thin omitted)

### Community 1 - "AdmissionController.php"
Cohesion: 0.12
Nodes (5): FormInput, ServiceAmount, DischargeController, DoctorController, WardController

### Community 2 - "Patient"
Cohesion: 0.11
Nodes (3): AdmissionController, PatientController, Patient

### Community 3 - "Admission"
Cohesion: 0.09
Nodes (3): SiteController, Admission, Discharge

### Community 4 - "composer.json"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 6 - "Admission-to-discharge workflow"
Cohesion: 0.25
Nodes (6): Admission-to-discharge workflow, Seven-table hospital database schema, Installation and Nginx execution, Live patient, doctor and ward selection, Hospital admission, services and discharge project, Project test commands

### Community 7 - "AdmissionService"
Cohesion: 0.13
Nodes (3): AdmissionServiceController, AdmissionService, Service

### Community 8 - "shamsi.js"
Cohesion: 0.34
Nodes (16): bounds(), button(), choose(), close(), display(), enhance(), gregorian(), iconButton() (+8 more)

### Community 11 - "install.sql"
Cohesion: 0.46
Nodes (7): admission_services, admissions, discharges, doctors, `patients`, services, wards

### Community 14 - "enhance"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 15 - "Yii2 Debug Toolbar Configuration"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 18 - "Historical Gregorian Date Input Explanation"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Knowledge Gaps
- **25 isolated node(s):** `name`, `description`, `type`, `license`, `php` (+20 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 145 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **24 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Patient` connect `Patient` to `yii\helpers\Html`, `AdmissionController.php`, `Admission`, `Doctor`, `AdmissionSearch`?**
  _High betweenness centrality (0.091) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Patient` (e.g. with `.search()` and `.search()`) actually correct?**
  _`Patient` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _25 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `yii\helpers\Html` be split into smaller, more focused modules?**
  _Cohesion score 0.05272108843537415 - nodes in this community are weakly interconnected._
- **Why does `Admission` connect `Admission` to `yii\helpers\Html`, `AdmissionController.php`, `Patient`, `Doctor`, `AdmissionService`, `AdmissionSearch`, `JalaliDate`?**
  _High betweenness centrality (0.088) - this node is a cross-community bridge._
- **Should `AdmissionController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.11724137931034483 - nodes in this community are weakly interconnected._
- **Why does `Doctor` connect `Doctor` to `yii\helpers\Html`, `AdmissionController.php`, `Patient`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._