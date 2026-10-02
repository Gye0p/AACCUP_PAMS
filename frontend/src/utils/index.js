export function getUrgencyClass(daysRemaining) {
  if (daysRemaining < 0 || daysRemaining < 8) return 'urgency-red'
  if (daysRemaining <= 30) return 'urgency-amber'
  return 'urgency-green'
}

export function getUrgencyLabel(daysRemaining) {
  if (daysRemaining < 0) return `${Math.abs(daysRemaining)} days overdue`
  if (daysRemaining === 0) return 'Due today'
  return `${daysRemaining} days left`
}

export function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  })
}

export function formatDateShort(dateString) {
  if (!dateString) return ''
  try {
    return new Date(dateString).toISOString().split('T')[0]
  } catch {
    return ''
  }
}

export const ROLE_LABELS = {
  ROLE_QUAMC_ADMIN: 'QUAMC Admin',
  ROLE_PRESIDENT: 'President',
  ROLE_CAMPUS_DIRECTOR: 'Campus Director',
  ROLE_VPAA: 'VPAA',
  ROLE_INTERNAL_ACCREDITOR: 'Internal Accreditor',
  ROLE_DEAN: 'Dean',
  ROLE_PROGRAM_HEAD: 'Program Head',
}

export const ALL_ROLES = Object.keys(ROLE_LABELS)

export const STATE_LABELS = {
  draft: 'Draft',
  submitted: 'Submitted',
  under_review: 'Under Review',
  needs_revision: 'Needs Revision',
  approved: 'Approved',
  completed: 'Completed',
}

export const STATE_COLORS = {
  draft: '#6c757d',
  submitted: '#0d6efd',
  under_review: '#fd7e14',
  needs_revision: '#dc3545',
  approved: '#198754',
  completed: '#0dcaf0',
}

export const AACCUP_AREAS = [
  { number: 'I',    name: 'Mission, Vision, Goals and Objectives' },
  { number: 'II',   name: 'Faculty' },
  { number: 'III',  name: 'Curriculum and Instruction' },
  { number: 'IV',   name: 'Support to Students' },
  { number: 'V',    name: 'Research' },
  { number: 'VI',   name: 'Extension and Community Involvement' },
  { number: 'VII',  name: 'Library' },
  { number: 'VIII', name: 'Physical Plant and Facilities' },
  { number: 'IX',   name: 'Laboratories' },
  { number: 'X',    name: 'Administration' },
]
