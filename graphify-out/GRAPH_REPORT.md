# Graph Report - hospital  (2026-10-21)

## Corpus Check
- 125 files · ~32,541 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 542 nodes · 859 edges · 72 communities (11 shown, 61 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 37 edges (avg confidence: 0.87)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Authorization and clinical access
- Hospital workflow concepts
- Treatment and ward models
- Composer and Yii dependencies
- Patient search and discharge
- Database schema and accounts
- Persian date picker
- Admission model relations
- Medical item catalog
- Admission search and input
- Service catalog management
- Prescription item model
- User identity sessions
- Staff integration tests
- Staff account provisioning
- Admission actions and lookup
- Doctor management
- Money amount calculations
- Site login and dashboard
- Jalali calendar conversion
- Persian date formatting
- User form validation
- Admission form references
- Bootstrap asset bundle
- Patient search JavaScript
- Discharge receipt snapshot
- Patient management actions
- User account management
- Login form validation
- Yii debug toolbar guidance
- Persian serial numbers
- Discharge actions
- Birth date history
- Calendar query tests
- Admission registration lock
- Clinical cost preview
- Medical catalog search
- Yii software license
- Font license terms
- Confirmation dialog

## God Nodes (most connected - your core abstractions)
1. `Admission` - 33 edges
2. `Access` - 29 edges
3. `User` - 20 edges
4. `Doctor` - 20 edges
5. `PrescriptionItem` - 18 edges
6. `render()` - 15 edges
7. `MedicalItem` - 14 edges
8. `Service` - 14 edges
9. `Patient` - 13 edges
10. `AdmissionController` - 13 edges

## Surprising Connections (you probably didn't know these)
- `Role-Based Access` --semantically_similar_to--> `AccessControl and Doctor Scope`  [INFERRED] [semantically similar]
  PRODUCT.md → docs/staff-guide.md
- `Treatment Revision Audit` --semantically_similar_to--> `Revision History`  [INFERRED] [semantically similar]
  PRODUCT.md → docs/staff-guide.md
- `Medical Item Catalog` --semantically_similar_to--> `Drug and Consumable Catalog`  [INFERRED] [semantically similar]
  PRODUCT.md → docs/staff-guide.md
- `Access.php Role Authorization` --semantically_similar_to--> `AccessControl and Doctor Scope`  [INFERRED] [semantically similar]
  README.md → docs/staff-guide.md
- `Active Doctor and Ward Selection` --semantically_similar_to--> `Doctor and Ward New-Admission Status`  [INFERRED] [semantically similar]
  README.md → docs/staff-guide.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Treatment to Discharge Flow** — readme_prescription, product_treatment_completion_approval, docs_staff_guide_discharge_queue, readme_discharge [INFERRED 0.85]
- **Historical Financial Integrity** — product_historical_pricing, readme_historical_pricing, product_immutable_discharge_receipt, readme_receipt_snapshot [INFERRED 0.85]

## Communities (72 total, 61 thin omitted)

### Community 0 - "Authorization and clinical access"
Cohesion: 0.07
Nodes (10): Access, AdmissionData, Billing, {closure#1}(), RoleController, AdmissionServiceController, ClinicalController, RecordChangeController (+2 more)

### Community 1 - "Hospital workflow concepts"
Cohesion: 0.08
Nodes (20): Drug and Consumable Catalog, Approved Discharge Queue, Financial Discharge Receipt, Patient Admission, Revision History, Staff Guide, Treatment Record Print, Hospital System (+12 more)

### Community 2 - "Treatment and ward models"
Cohesion: 0.08
Nodes (4): WardController, AdmissionService, Prescription, Ward

### Community 4 - "Composer and Yii dependencies"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 5 - "Patient search and discharge"
Cohesion: 0.10
Nodes (3): Discharge, Patient, PatientSearch

### Community 6 - "Database schema and accounts"
Cohesion: 0.19
Nodes (17): admission_services, admissions, discharges, doctors, medical_items, `patients`, prescription_items, prescriptions (+9 more)

### Community 7 - "Persian date picker"
Cohesion: 0.34
Nodes (16): bounds(), button(), choose(), close(), display(), enhance(), gregorian(), iconButton() (+8 more)

### Community 15 - "Staff integration tests"
Cohesion: 0.26
Nodes (5): Browser, check(), correctionPayload(), createStaff(), readyPayload()

### Community 29 - "Patient search JavaScript"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 35 - "Yii debug toolbar guidance"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 40 - "Birth date history"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Knowledge Gaps
- **29 isolated node(s):** `yiisoft/yii2-composer`, `process-timeout`, `description`, `enabled`, `license` (+24 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 251 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **61 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Access` connect `Authorization and clinical access` to `Admission list interface`, `Admission model relations`, `Navigation bar`, `User identity sessions`, `Admission actions and lookup`, `Money amount calculations`, `Site login and dashboard`, `Patient validation helpers`?**
  _High betweenness centrality (0.074) - this node is a cross-community bridge._
- **What connects `yiisoft/yii2-composer`, `process-timeout`, `description` to the rest of the system?**
  _29 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Authorization and clinical access` be split into smaller, more focused modules?**
  _Cohesion score 0.07268170426065163 - nodes in this community are weakly interconnected._
- **Why does `User` connect `User identity sessions` to `Login form validation`, `Treatment and ward models`, `Staff account provisioning`, `User form validation`, `User list interface`?**
  _High betweenness centrality (0.054) - this node is a cross-community bridge._
- **Should `Hospital workflow concepts` be split into smaller, more focused modules?**
  _Cohesion score 0.07557354925775979 - nodes in this community are weakly interconnected._
- **Why does `Admission` connect `Admission model relations` to `Authorization and clinical access`, `Treatment and ward models`, `Discharge actions`, `Admission registration lock`, `Admission actions and lookup`, `Doctor management`, `Site login and dashboard`, `Patient validation helpers`, `Discharge receipt snapshot`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Should `Treatment and ward models` be split into smaller, more focused modules?**
  _Cohesion score 0.082010582010582 - nodes in this community are weakly interconnected._