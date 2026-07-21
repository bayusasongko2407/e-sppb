import re

with open('/www/wwwroot/e-sppb-enterprise/docs/29_ENTERPRISE_API_BLUEPRINT.md', 'r') as f:
    content = f.read()

# Replace Database Overview table
db_table_old = r"\| Table \| Primary Key \| Foreign Key \| Unique Key \| Index \| Soft Delete \| Timestamp \| Audit Field \| Relationships \|\n\|---\|---\:\|---\|---\|---\|---\|---\|---\|---\|\n(?:\|[^\n]+\n)*"

db_table_new = """| Table | Primary Key | Foreign Key | Unique Key | Index | Soft Delete | Timestamp | Audit Field | Relationships |
|---|---:|---|---|---|---|---|---|---|
| plants | id | - | code | is_active | No | Yes | created_by, updated_by bila tersedia | hasMany departments, locations, users, assets, sppb_headers |
| departments | id | plant_id | plant_id, code | plant_id, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo plant; hasMany users, sppb_headers |
| locations | id | plant_id | plant_id, code | name, plant_id, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo plant; hasMany assets |
| units | id | - | code | is_active | No | Yes | created_by, updated_by bila tersedia | hasMany items, sppb_details |
| items | id | unit_id | code | item_category, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo unit; hasMany sppb_details |
| assets | id | plant_id, location_id, unit_id | barcode | plant_id, location_id, status, is_active | No | Yes | created_by, updated_by bila tersedia | belongsTo plant, location, unit; hasMany sppb_details |
| positions | id | - | code | is_active | No | Yes | created_by, updated_by | hasMany user_positions |
| user_positions | id | user_id, position_id | user_id, position_id | - | No | Yes | - | belongsTo user, position |
| users | id | plant_id, department_id, manager_id | nik, email | plant_id, department_id, is_active | No | Yes | last_login_at | belongsTo plant, department, manager |
| email_change_requests | id | user_id | - | status | No | Yes | - | belongsTo user |
| roles | id | - | - | - | No | Yes | - | Spatie RBAC |
| permissions | id | - | - | - | No | Yes | - | Spatie RBAC |
| enum_controls | id | - | - | table_name, column_name | No | Yes | - | - |
| workflow_templates | id | plant_id, department_id | uuid; code, version | document_type, plant_id, department_id, is_active | No | Yes | created_by bila tersedia | hasMany workflow_steps, workflow_instances |
| workflow_steps | id | workflow_template_id, approver_user_id, approver_position_id | workflow_template_id, sequence; workflow_template_id, code | approver_user_id, approver_position_id | No | Yes | - | belongsTo workflow_template |
| workflow_delegations | id | delegator_id, delegate_id, plant_id | - | delegator_id/date/status; delegate_id/date/status | No | Yes | created_by bila tersedia | belongsTo delegator, delegate, plant |
| sppb_headers | id | plant_id, department_id, requester_id, origin_location_id, destination_location_id, current_workflow_instance_id, current_approver_id | uuid, document_number | requester/status/date; plant/status/date_needed; current_approver_id | Yes | Yes | submitted_by, approved_by, rejected_by, cancelled_by bila tersedia | hasMany details, attachments, workflow_instances, status_logs, goods_releases |
| sppb_details | id | sppb_header_id, item_id, asset_id, unit_id | sppb_header_id, line_no | item_id, asset_id, unit_id | No | Yes | created_by, updated_by bila tersedia | belongsTo sppb_header, item, asset, unit, goods_release_items |
| workflow_instances | id | workflow_template_id, sppb_header_id | uuid; sppb_header_id, revision_no | status, current_sequence | No | Yes | - | belongsTo sppb_header; hasMany workflow_instance_steps |
| workflow_instance_steps | id | workflow_instance_id, workflow_step_id, acted_by_id | workflow_instance_id, sequence | status, due_at | No | Yes | acted_by_id, acted_at | belongsTo workflow_instance; hasMany workflow_step_approvers |
| workflow_step_approvers | id | workflow_instance_step_id, approver_id, delegated_from_id | workflow_instance_step_id, approver_id | approver_id, status | No | Yes | acted_at | belongsTo step, approver, delegated_from |
| workflow_commands | id | actor_id | command_uuid | aggregate_type, aggregate_id, status | No | Yes | actor_id | belongsTo actor |
| sppb_status_logs | id | sppb_header_id, workflow_instance_id, workflow_instance_step_id, actor_id | - | sppb_header_id, logged_at; command_uuid, action | No | Yes | actor_id, command_uuid, correlation_id | belongsTo sppb, workflow, step, actor |
| attachments | id | sppb_header_id, uploader_id | uuid, stored_name | sppb_header_id, created_at; checksum_sha256 | Yes | Yes | uploader_id | belongsTo sppb_header, uploader |
| goods_releases | id | sppb_header_id, plant_id, released_by_id | uuid, release_number | status, created_at | No | Yes | created_by_id, received_by_id | belongsTo sppb_header; hasMany goods_release_items |
| goods_release_items | id | goods_release_id, sppb_detail_id | - | is_checked | No | Yes | - | belongsTo goods_release, sppb_detail |
| document_templates | id | plant_id | uuid, code | document_type, is_active | No | Yes | created_by_id | hasMany document_generations |
| document_generations | id | document_template_id, plant_id, sppb_header_id, goods_release_id, generated_by_id | uuid, command_uuid, stored_name | plant_id, status, generated_at | No | Yes | generated_by_id, revoked_by_id | belongsTo sppb_header or goods_release; hasMany document_validations, document_pages |
| document_pages | id | document_generation_id | verification_uuid, qr_payload_checksum | page_checksum_sha256 | No | Yes | - | belongsTo document_generation; hasMany document_validations |
| document_validations | id | document_generation_id, document_page_id, actor_id | uuid | validation_result, verified_at | No | Yes | actor_id, ip hash, user agent hash | belongsTo document_generation, page, actor |
| document_accesses | id | user_id, plant_id | - | module, is_active | No | Yes | - | belongsTo user, plant |
| activity_logs | id | user_id | - | module, action, reference, created_at | No | Yes | user_id, ip_address, user_agent | belongsTo user |
"""

content = re.sub(db_table_old, db_table_new, content)

with open('/www/wwwroot/e-sppb-enterprise/docs/29_ENTERPRISE_API_BLUEPRINT.md', 'w') as f:
    f.write(content)

