import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/ld+json' },
})

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
export const fetchColleges = () => api.get('/colleges').then(r => r.data['hydra:member'])

// ── Programs ──────────────────────────────────────────────────────
export const fetchPrograms = () => api.get('/programs').then(r => r.data['hydra:member'])

// ── Accreditation Cycles ──────────────────────────────────────────
export const fetchCycles = () => api.get('/accreditation_cycles').then(r => r.data['hydra:member'])
export const fetchCycle = (id) => api.get(`/accreditation_cycles/${id}`).then(r => r.data)
export const createCycle = (data) => api.post('/accreditation_cycles', data)

// ── Area Assignments ──────────────────────────────────────────────
export const fetchAreaAssignment = (id) => api.get(`/area_assignments/${id}`).then(r => r.data)
export const submitIaReview = (id, data) =>
  api.patch(`/area-assignments/${id}/ia-review`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } })

// ── Compliance Activities ─────────────────────────────────────────
export const fetchActivities = (areaAssignmentId) =>
  api.get(`/compliance_activities?areaAssignment=${areaAssignmentId}`).then(r => r.data['hydra:member'])
export const createActivity = (data) => api.post('/compliance_activities', data)
export const updateActivity = (id, data) =>
  api.patch(`/compliance_activities/${id}`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } })

// ── Evidence ──────────────────────────────────────────────────────
export const uploadEvidence = (activityId, file, originalName) => {
  const formData = new FormData()
  formData.append('file', file)
  formData.append('activity', `/api/compliance_activities/${activityId}`)
  formData.append('originalName', originalName)
  return api.post('/evidence', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
}

// ── Gantt ─────────────────────────────────────────────────────────
export const fetchGantt = (cycleId) => api.get(`/gantt/${cycleId}`).then(r => r.data)

// ── Users (Admin) ─────────────────────────────────────────────────
export const fetchUsers = () => api.get('/users').then(r => r.data['hydra:member'])
export const createUser = (data) => api.post('/users', data)
export const updateUser = (id, data) =>
  api.patch(`/users/${id}`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } })

// ── SAR Recommendations ───────────────────────────────────────────
export const fetchSarRecommendations = (areaAssignmentId) =>
  api.get(`/sar_recommendations?areaAssignment=${areaAssignmentId}`).then(r => r.data['hydra:member'])

export default api
