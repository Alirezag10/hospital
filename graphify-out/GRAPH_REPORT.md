# Graph Report - hospital  (2026-10-10)

## Corpus Check
- 61 files · ~108,859 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 6 file(s) not represented in the graph (top: (none) 2, .css 2, .woff2 1)
- Graph health warning: 2 self-loop edges were found; no missing or dangling endpoints.

## Summary
- 303 nodes · 452 edges · 22 communities (10 shown, 12 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 19 edges (avg confidence: 0.86)
- Token cost: unavailable for semantic extraction by Codex subagents (0 input · 0 output recorded by Graphify).

## Community Hubs (Navigation)
- Persistence and Shared Models
- Admission and Patient Views
- Admission and Discharge Controllers
- Patient and Admission Actions
- Ward and Discharge Workflows
- Project Guides and Care Flows
- Composer Dependencies
- Jalali Date and UI Scripts
- Hospital SQL Schema
- Admission Service Model
- Frontend Asset Bundle
- Patient Search UI
- Yii Debug Toolbar Guidance
- Yii Software License
- Font License
- Confirmation Dialog

## God Nodes (most connected - your core abstractions)
1. `Admission` - 26 edges
2. `Patient` - 22 edges
3. `Doctor` - 12 edges
4. `render()` - 12 edges
5. `Hospital Admission and Discharge System` - 12 edges
6. `Ward` - 11 edges
7. `enhance()` - 11 edges
8. `AdmissionController` - 10 edges
9. `Hospital Project Work Report` - 10 edges
10. `AdmissionService` - 8 edges

## Surprising Connections (you probably didn't know these)
- `Historical Gregorian Date Input Explanation` --conceptually_related_to--> `Jalali Date Picker and Submit Time Validation`  [INFERRED]
  graphify-out/memory/query_20261009_124939_4709aae5_چرا_قسمت_ثبت_بیمار_تاریخ_تولد_را_میلادی_می_گیرد.md → WORK_REPORT.md
- `{closure#1}()` --references--> `Ward`  [EXTRACTED]
  app/views/admission/_form.php → app/models/Ward.php
- `Hospital Admission and Discharge System` --semantically_similar_to--> `Hospital Project Work Report`  [INFERRED] [semantically similar]
  README.md → WORK_REPORT.md
- `Admission Workflow` --semantically_similar_to--> `Admission and Historical Doctor Ward Names`  [INFERRED] [semantically similar]
  README.md → WORK_REPORT.md
- `Discharge Cost Calculation` --semantically_similar_to--> `Server Calculated Service Pricing and Discharge`  [INFERRED] [semantically similar]
  README.md → WORK_REPORT.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Patient to Discharge Care Path** — project_analysis_patient_flow, project_analysis_admission_flow, project_analysis_service_flow, project_analysis_discharge_flow [EXTRACTED 1.00]
- **Financial Consistency** — project_analysis_service_flow, project_analysis_discharge_flow, project_analysis_historic_price, project_analysis_transaction, project_analysis_row_lock [EXTRACTED 1.00]

## Communities (22 total, 12 thin omitted)

### Community 0 - "Persistence and Shared Models"
Cohesion: 0.06
Nodes (34): Record Access Control, ActiveForm, Yii ActiveRecord, Admission Registration Flow, Admission ActiveRecord, AdmissionService ActiveRecord, Admission Services Table, Admissions Table (+26 more)

### Community 2 - "Admission and Discharge Controllers"
Cohesion: 0.08
Nodes (5): AdmissionServiceController, DischargeController, DoctorController, SiteController, Admission

### Community 3 - "Patient and Admission Actions"
Cohesion: 0.08
Nodes (5): AdmissionController, PatientController, AdmissionSearch, Patient, PatientSearch

### Community 4 - "Ward and Discharge Workflows"
Cohesion: 0.08
Nodes (5): WardController, Discharge, Doctor, Service, Ward

### Community 5 - "Project Guides and Care Flows"
Cohesion: 0.12
Nodes (24): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input, Admission Services and Historical Pricing, Admission Workflow, Discharge Cost Calculation, Hospital Admission and Discharge System, MySQL MariaDB Database (+16 more)

### Community 6 - "Composer Dependencies"
Cohesion: 0.10
Nodes (20): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+12 more)

### Community 7 - "Jalali Date and UI Scripts"
Cohesion: 0.34
Nodes (14): button(), choose(), close(), display(), enhance(), gregorian(), iconButton(), init() (+6 more)

### Community 8 - "Hospital SQL Schema"
Cohesion: 0.46
Nodes (7): admission_services, admissions, discharges, doctors, `patients`, services, wards

### Community 11 - "Patient Search UI"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 12 - "Yii Debug Toolbar Guidance"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

## Knowledge Gaps
- **28 isolated node(s):** `AppAsset Bundle`, `Eager Loading`, `Database Install SQL`, `Font Redistribution Conditions`, `SIL Open Font License 1.1` (+23 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 125 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **12 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Admission` connect `Admission and Discharge Controllers` to `Admission and Patient Views`, `Patient and Admission Actions`, `Ward and Discharge Workflows`?**
  _High betweenness centrality (0.072) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Patient` (e.g. with `.search()` and `.search()`) actually correct?**
  _`Patient` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `AppAsset Bundle`, `Eager Loading`, `Database Install SQL` to the rest of the system?**
  _28 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Persistence and Shared Models` be split into smaller, more focused modules?**
  _Cohesion score 0.06205673758865248 - nodes in this community are weakly interconnected._
- **Why does `Patient` connect `Patient and Admission Actions` to `Admission and Patient Views`, `Admission and Discharge Controllers`, `Ward and Discharge Workflows`?**
  _High betweenness centrality (0.067) - this node is a cross-community bridge._
- **Should `Admission and Patient Views` be split into smaller, more focused modules?**
  _Cohesion score 0.06201550387596899 - nodes in this community are weakly interconnected._
- **Why does `Doctor` connect `Ward and Discharge Workflows` to `Admission and Patient Views`, `Admission and Discharge Controllers`, `Patient and Admission Actions`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._