# Graph Report - hospital  (2026-10-16)

## Corpus Check
- 50 files · ~22,672 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 325 nodes · 495 edges · 28 communities (12 shown, 16 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 19 edges (avg confidence: 0.85)
- Token cost: unavailable for this run (semantic subagent usage was not reported)

## Community Hubs (Navigation)
- Admission and Ward Views
- Controllers and Input Guards
- Jalali Date Conversion
- Admission ActiveRecord Model
- Composer Dependency Setup
- Project Validation Script
- Admission View Documentation
- Admission Service Workflow
- Jalali Calendar Interface
- Admission Search Model
- HTTP Test Utilities
- Hospital Database Schema
- Hospital Display Formatting
- Frontend Asset Bundle
- Patient Live Search
- Yii Debug Guidance
- Persian Grid Numbering
- Birth Date History
- Yii Software License
- Font Redistribution License

## God Nodes (most connected - your core abstractions)
1. `Patient` - 30 edges
2. `expect()` - 21 edges
3. `AdmissionService` - 15 edges
4. `FormInput` - 14 edges
5. `render()` - 14 edges
6. `action()` - 14 edges
7. `Admission` - 13 edges
8. `Service` - 11 edges
9. `enhance()` - 11 edges
10. `AdmissionController` - 10 edges

## Surprising Connections (you probably didn't know these)
- `Live patient, doctor and ward selection` --semantically_similar_to--> `AJAX entity selection`  [INFERRED] [semantically similar]
  README.md → docs/admission-views-guide.html
- `{closure#10}()` --calls--> `AdmissionService`  [EXTRACTED]
  scripts/check-project.php → app/models/AdmissionService.php
- `{closure#11}()` --calls--> `AdmissionService`  [EXTRACTED]
  scripts/check-project.php → app/models/AdmissionService.php
- `{closure#15}()` --calls--> `AdmissionService`  [EXTRACTED]
  scripts/check-project.php → app/models/AdmissionService.php
- `{closure#3}()` --calls--> `Patient`  [EXTRACTED]
  scripts/check-project.php → app/models/Patient.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Admission input security flow** — docs_admission_views_guide_admission_partial_form, docs_admission_views_guide_locked_patient_id, docs_project_test_report_form_input_guard [INFERRED 0.85]

## Communities (28 total, 16 thin omitted)

### Community 1 - "Controllers and Input Guards"
Cohesion: 0.11
Nodes (6): FormInput, DischargeController, DoctorController, PatientController, SiteController, WardController

### Community 2 - "Jalali Date Conversion"
Cohesion: 0.08
Nodes (6): JalaliDate, AdmissionController, Patient, {closure#4}(), {closure#6}(), nationalCode()

### Community 3 - "Admission ActiveRecord Model"
Cohesion: 0.08
Nodes (4): Admission, Discharge, Doctor, Ward

### Community 4 - "Composer Dependency Setup"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 5 - "Project Validation Script"
Cohesion: 0.23
Nodes (20): action(), {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}() (+12 more)

### Community 6 - "Admission View Documentation"
Cohesion: 0.11
Nodes (12): Admission partial form, Yii 2 admission views guide, AJAX entity selection, Yii MVC request flow, HTTP route, CSRF and invalid-request tests, Project test report, Admission-to-discharge workflow, Seven-table hospital database schema (+4 more)

### Community 7 - "Admission Service Workflow"
Cohesion: 0.13
Nodes (3): AdmissionServiceController, AdmissionService, Service

### Community 8 - "Jalali Calendar Interface"
Cohesion: 0.36
Nodes (15): button(), choose(), close(), display(), enhance(), gregorian(), iconButton(), init() (+7 more)

### Community 11 - "Hospital Database Schema"
Cohesion: 0.46
Nodes (7): admission_services, admissions, discharges, doctors, `patients`, services, wards

### Community 14 - "Patient Live Search"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 15 - "Yii Debug Guidance"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 18 - "Birth Date History"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Knowledge Gaps
- **23 isolated node(s):** `Yii Software LLC`, `Yii Software License`, `Font Redistribution Conditions`, `SIL Open Font License 1.1`, `Debug Toolbar Allowed IPs` (+18 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 147 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Patient` connect `Jalali Date Conversion` to `Admission and Ward Views`, `Controllers and Input Guards`, `Admission ActiveRecord Model`, `Project Validation Script`, `Admission Search Model`?**
  _High betweenness centrality (0.115) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Patient` (e.g. with `.search()` and `.search()`) actually correct?**
  _`Patient` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Yii Software LLC`, `Yii Software License`, `Font Redistribution Conditions` to the rest of the system?**
  _23 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admission and Ward Views` be split into smaller, more focused modules?**
  _Cohesion score 0.05357142857142857 - nodes in this community are weakly interconnected._
- **Why does `Admission` connect `Admission ActiveRecord Model` to `Controllers and Input Guards`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Should `Controllers and Input Guards` be split into smaller, more focused modules?**
  _Cohesion score 0.10793650793650794 - nodes in this community are weakly interconnected._
- **Why does `Service` connect `Admission Service Workflow` to `Admission and Ward Views`, `Controllers and Input Guards`, `Admission ActiveRecord Model`, `Project Validation Script`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
