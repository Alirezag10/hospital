# Graph Report - hospital  (2026-10-06)

## Corpus Check
- 41 files · ~99,841 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 5 file(s) not represented in the graph (top: (none) 2, .css 1, .woff2 1)

## Summary
- 205 nodes · 282 edges · 14 communities (7 shown, 7 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 4 edges (avg confidence: 0.88)
- Token cost: unavailable for semantic subagents (0 input · 0 output recorded by Graphify).

## Community Hubs (Navigation)
- Patient Admission Controllers
- Admission Views and Forms
- Services and Discharge Models
- Validation and Security
- Persistence and Database
- Composer Dependencies
- Deployment and Care Workflows
- Frontend Asset Bundle
- SQL Table Schema
- Yii Software License

## God Nodes (most connected - your core abstractions)
1. `Admission` - 22 edges
2. `Discharge` - 15 edges
3. `Patient` - 13 edges
4. `AdmissionService` - 11 edges
5. `Service` - 9 edges
6. `Project Analysis Report` - 8 edges
7. `MariaDB Database` - 8 edges
8. `Discharge Flow` - 8 edges
9. `PatientSearch` - 7 edges
10. `Patient ActiveRecord` - 7 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --references--> `Admission`  [EXTRACTED]
  app/views/discharge/_form.php → app/models/Admission.php
- `Server-side Discharge Calculation` --semantically_similar_to--> `Server-side Historical Pricing`  [INFERRED] [semantically similar]
  README.md → WORK_REPORT.md
- `Five-table Hospital Schema` --semantically_similar_to--> `Five-table Hospital Schema`  [INFERRED] [semantically similar]
  README.md → WORK_REPORT.md
- `Local Yekan Font` --references--> `SIL Open Font License 1.1`  [EXTRACTED]
  README.md → app/web/fonts/LICENSE.txt
- `Local Yekan Font` --conceptually_related_to--> `Responsive Persian User Interface`  [EXTRACTED]
  README.md → WORK_REPORT.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Patient to Discharge Care Path** — project_analysis_patient_flow, project_analysis_admission_flow, project_analysis_service_flow, project_analysis_discharge_flow [EXTRACTED 1.00]
- **Financial Consistency** — project_analysis_service_flow, project_analysis_discharge_flow, project_analysis_historic_price, project_analysis_transaction, project_analysis_row_lock [EXTRACTED 1.00]

## Communities (14 total, 7 thin omitted)

### Community 0 - "Patient Admission Controllers"
Cohesion: 0.08
Nodes (6): AdmissionController, PatientController, SiteController, Admission, Patient, PatientSearch

### Community 2 - "Services and Discharge Models"
Cohesion: 0.08
Nodes (5): AdmissionServiceController, DischargeController, AdmissionService, Discharge, Service

### Community 3 - "Validation and Security"
Cohesion: 0.10
Nodes (16): Record Access Control, ActiveForm, Admission Registration Flow, AppAsset Bundle, Application Configuration, CSRF Validation, GridView, MVC Architecture (+8 more)

### Community 4 - "Persistence and Database"
Cohesion: 0.14
Nodes (18): Yii ActiveRecord, Admission ActiveRecord, AdmissionService ActiveRecord, Admission Services Table, Admissions Table, MariaDB Database, Discharge Flow, Discharge ActiveRecord (+10 more)

### Community 5 - "Composer Dependencies"
Cohesion: 0.11
Nodes (18): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+10 more)

### Community 6 - "Deployment and Care Workflows"
Cohesion: 0.13
Nodes (16): Font Redistribution Conditions, SIL Open Font License 1.1, Server-side Discharge Calculation, Five-table Hospital Schema, Hospital Admission, Services and Discharge System, Nginx and PHP FastCGI Deployment, Patient Registration and Admission Workflow, Local Yekan Font (+8 more)

### Community 8 - "SQL Table Schema"
Cohesion: 0.60
Nodes (5): admission_services, admissions, discharges, `patients`, services

## Knowledge Gaps
- **22 isolated node(s):** `name`, `description`, `type`, `license`, `php` (+17 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 96 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **7 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Admission` connect `Patient Admission Controllers` to `Admission Views and Forms`, `Services and Discharge Models`?**
  _High betweenness centrality (0.074) - this node is a cross-community bridge._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _22 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Patient Admission Controllers` be split into smaller, more focused modules?**
  _Cohesion score 0.08253968253968254 - nodes in this community are weakly interconnected._
- **Why does `Patient` connect `Patient Admission Controllers` to `Admission Views and Forms`, `Services and Discharge Models`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Should `Admission Views and Forms` be split into smaller, more focused modules?**
  _Cohesion score 0.07386363636363637 - nodes in this community are weakly interconnected._
- **Why does `Discharge` connect `Services and Discharge Models` to `Patient Admission Controllers`, `Admission Views and Forms`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Should `Services and Discharge Models` be split into smaller, more focused modules?**
  _Cohesion score 0.0846774193548387 - nodes in this community are weakly interconnected._