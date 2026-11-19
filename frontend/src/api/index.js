import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/ld+json' },
})

const fetchCollection = async (path) => {
  const members = []
  let next = path

  while (next) {
    const requestPath = next.startsWith('/api/')
      ? next.slice(4)
      : next
    const response = await api.get(requestPath)
    const data = response.data
    members.push(...(data['hydra:member'] || data.member || []))
    next = data['hydra:view']?.next || data.view?.next || null
  }

  return members
}

// Attach JWT token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('jwt_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Auto-logout on 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('jwt_token')
      localStorage.removeItem('jwt_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// ── Auth ──────────────────────────────────────────────────────────
export const login = (email, password) =>
  axios.post('/api/auth/login', { email, password }, { headers: { 'Content-Type': 'application/json' } })

// ── Dashboard ─────────────────────────────────────────────────────
export const fetchDashboard = () => api.get('/dashboard').then(r => r.data)

// ── Colleges ──────────────────────────────────────────────────────
export const fetchColleges = () => fetchCollection('/colleges')

// ── Programs ──────────────────────────────────────────────────────
export const fetchPrograms = () => fetchCollection('/programs')
export const fetchAreas = () => fetchCollection('/aaccup_areas')

// ── Accreditation Cycles ──────────────────────────────────────────
export const fetchCycles = () => fetchCollection('/accreditation_cycles')
export const fetchCycle = (id) => api.get(`/accreditation_cycles/${id}`).then(r => r.data)
export const createCycle = (data) => api.post('/accreditation_cycles', data)

// ── Area Assignments ──────────────────────────────────────────────
export const fetchAreaAssignment = (id) => api.get(`/area_assignments/${id}`).then(r => r.data)
export const fetchAreaAssignments = () => fetchCollection('/area_assignments')
export const submitIaReview = (id, data) =>
  api.patch(`/area-assignments/${id}/ia-review`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } })

// ── Compliance Activities ─────────────────────────────────────────
export const fetchActivities = (areaAssignmentId) =>
  api.get(`/compliance_activities?areaAssignment=${areaAssignmentId}`).then(r => r.data['hydra:member'])
export const fetchActivity = (id) => api.get(`/compliance_activities/${id}`).then(r => r.data)
export const createActivity = (data) => api.post('/compliance_activities', data)
export const updateActivity = (id, data) =>
  api.patch(`/compliance_activities/${id}`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } })
export const deleteActivity = (id) => api.delete(`/compliance_activities/${id}`)

// ── Evidence ──────────────────────────────────────────────────────
export const uploadEvidence = (activityId, file, originalName) => {
  const formData = new FormData()
  formData.append('file', file)
  formData.append('activityId', activityId)
  formData.append('originalName', originalName)
  return api.post('/evidence/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
}

export const uploadAreaEvidence = (areaAssignmentId, file, originalName) => {
  const formData = new FormData()
  formData.append('file', file)
  formData.append('areaAssignmentId', areaAssignmentId)
  formData.append('originalName', originalName)
  return api.post('/evidence/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
}

// ── Gantt ─────────────────────────────────────────────────────────
export const fetchGantt = (cycleId) => api.get(`/gantt/${cycleId}`).then(r => r.data)
export const downloadReport = (cycleId) =>
  api.get(`/report/${cycleId}`, { responseType: 'blob' }).then((response) => {
    const url = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.download = `AACCUP_Report_${cycleId}.pdf`
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  })

// ── Users (Admin) ─────────────────────────────────────────────────
export const fetchUsers = () => fetchCollection('/users')
export const createUser = (data) => api.post('/users', data)
export const updateUser = (id, data) =>
  api.patch(`/users/${id}`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } })
export const createInternalAccreditorAssignment = (data) => api.post('/internal_accreditor_assignments', data)
export const fetchInternalAccreditorAssignments = () =>
  api.get('/internal_accreditor_assignments').then(r => r.data['hydra:member'])
export const deleteInternalAccreditorAssignment = (id) => api.delete(`/internal_accreditor_assignments/${id}`)

// ── SAR Recommendations ───────────────────────────────────────────
export const fetchSarRecommendations = (areaAssignmentId) =>
  api.get(`/sar_recommendations?areaAssignment=${areaAssignmentId}`)
    .then(r => r.data['hydra:member'] || r.data.member || [])

// ── Notifications ──────────────────────────────────────────────────────
export const fetchNotifications = () =>
  api.get('/notifications').then(r => r.data['hydra:member'])
export const fetchUnreadCount = () =>
  api.get('/notifications/unread-count').then(r => r.data.count)
export const markNotificationRead = (id) =>
  api.patch(`/notifications/${id}`, { readAt: new Date().toISOString() }, { headers: { 'Content-Type': 'application/merge-patch+json' } })

// ── Area Coordinator Assignments ─────────────────────────────────
export const fetchAreaCoordinatorAssignments = () =>
  api.get('/area_coordinator_assignments').then(r => r.data['hydra:member'])
export const createAreaCoordinatorAssignment = (data) =>
  api.post('/area_coordinator_assignments', data)
export const deleteAreaCoordinatorAssignment = (id) =>
  api.delete(`/area_coordinator_assignments/${id}`)

// ── SAR Recommendations (update compliance/remarks) ─────────────────
export const updateSarRecommendation = (id, data) =>
  api.patch(`/sar_recommendations/${id}`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } })

export default api
