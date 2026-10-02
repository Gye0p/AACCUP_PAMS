import { useState, useRef } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { uploadEvidence } from '../api'
import Layout from '../components/Layout'
import { useAuth } from '../context/useAuth'

export default function EvidenceUploadPage() {
  const { activityId } = useParams()
  const navigate = useNavigate()
  const { isProgramHead, isAdmin } = useAuth()
  const fileInputRef = useRef(null)

  const [file, setFile] = useState(null)
  const [originalName, setOriginalName] = useState('')
  const [isUploading, setIsUploading] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [isDragOver, setIsDragOver] = useState(false)

  const handleFileChange = (selected) => {
    if (!selected) return
    setFile(selected)
    if (!originalName) setOriginalName(selected.name)
  }

  const handleInputChange = (e) => handleFileChange(e.target.files[0])

  const handleDrop = (e) => {
    e.preventDefault()
    setIsDragOver(false)
    const dropped = e.dataTransfer.files[0]
    if (dropped) handleFileChange(dropped)
  }

  const handleDragOver = (e) => { e.preventDefault(); setIsDragOver(true) }
  const handleDragLeave = () => setIsDragOver(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!file) return

    setIsUploading(true)
    setMessage('')
    setError('')

    try {
      await uploadEvidence(activityId, file, originalName)
      setMessage('Evidence uploaded successfully.')
      setFile(null)
      setOriginalName('')
      if (fileInputRef.current) fileInputRef.current.value = ''
    } catch (err) {
      setError(err.response?.data?.detail || 'Failed to upload evidence. Please try again.')
    } finally {
      setIsUploading(false)
    }
  }

  const canUpload = isProgramHead() || isAdmin()

  const formatFileSize = (bytes) => {
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
  }

  return (
    <Layout>
      {/* Back */}
      <button
        onClick={() => navigate(-1)}
        className="flex items-center gap-1.5 text-[12px] text-[#8a8a8a] hover:text-[#b0b0b0] transition-colors mb-5"
      >
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
          <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
      </button>

      <div className="mb-6">
        <h1 className="text-xl font-semibold text-white" style={{ fontFamily: 'Poppins, sans-serif' }}>
          Upload Evidence
        </h1>
        <p className="text-[13px] text-[#8a8a8a] mt-1">
          Compliance Activity #{activityId}
        </p>
      </div>

      <div className="max-w-lg">
        {!canUpload ? (
          <div className="bg-[#1a1a1a] border border-[#252525] rounded-md px-5 py-8 text-center">
            <div className="flex items-center justify-center w-10 h-10 bg-[#1e1e1e] rounded-md mx-auto mb-3">
              <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6b6b6b" strokeWidth="1.5">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <p className="text-[13px] text-[#8a8a8a]">You do not have permission to upload evidence.</p>
          </div>
        ) : (
          <>
            {message && (
              <div className="flex items-start gap-2.5 bg-[#0f1e10] border border-[#1a4a1a] rounded px-4 py-3 mb-5">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#4ade80" strokeWidth="2" className="flex-shrink-0 mt-0.5">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span className="text-[13px] text-[#4ade80]">{message}</span>
              </div>
            )}

            {error && (
              <div className="flex items-start gap-2.5 bg-[#1e1212] border border-[#4a1a1a] rounded px-4 py-3 mb-5">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#f87171" strokeWidth="2" className="flex-shrink-0 mt-0.5">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span className="text-[13px] text-[#f87171]">{error}</span>
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              {/* Drop zone */}
              <div
                onDrop={handleDrop}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onClick={() => fileInputRef.current?.click()}
                className={`
                  relative border-2 border-dashed rounded-md px-6 py-10 text-center cursor-pointer
                  transition-all duration-150
                  ${isDragOver
                    ? 'border-[#76ff03] bg-[#76ff03]/5'
                    : file
                    ? 'border-[#2a4a1a] bg-[#0f1e10]'
                    : 'border-[#2e2e2e] bg-[#1a1a1a] hover:border-[#424242] hover:bg-[#1e1e1e]'
                  }
                `}
              >
                <input
                  ref={fileInputRef}
                  id="file-upload"
                  type="file"
                  onChange={handleInputChange}
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                  className="sr-only"
                />

                {file ? (
                  <div className="flex items-center justify-center gap-3">
                    <div className="flex items-center justify-center w-10 h-10 bg-[#76ff03]/10 rounded">
                      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#76ff03" strokeWidth="1.75">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </div>
                    <div className="text-left">
                      <p className="text-[13px] font-medium text-[#d0d0d0] truncate max-w-xs">{file.name}</p>
                      <p className="text-[11px] text-[#6b6b6b]">{formatFileSize(file.size)}</p>
                    </div>
                  </div>
                ) : (
                  <>
                    <div className="flex items-center justify-center w-10 h-10 bg-[#1e1e1e] rounded mx-auto mb-3">
                      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6b6b6b" strokeWidth="1.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                      </svg>
                    </div>
                    <p className="text-[13px] text-[#8a8a8a]">
                      Drop a file here, or <span className="text-[#76ff03]">click to browse</span>
                    </p>
                    <p className="text-[11px] text-[#6b6b6b] mt-1">
                      PDF, DOCX, XLSX, PPTX, JPG, PNG — max 20 MB
                    </p>
                  </>
                )}
              </div>

              {/* Display name */}
              <div>
                <label className="block text-[12px] font-medium text-[#b0b0b0] mb-1.5">
                  Display Name <span className="text-[#6b6b6b] font-normal">(optional)</span>
                </label>
                <input
                  type="text"
                  value={originalName}
                  onChange={e => setOriginalName(e.target.value)}
                  placeholder="e.g. Faculty_Manual_2024.pdf"
                  className="
                    w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded
                    px-3 py-2.5 text-[13px] text-white placeholder-[#4a4a4a]
                    focus:outline-none focus:border-[#76ff03] focus:ring-1 focus:ring-[#76ff03]/20
                    transition-colors duration-150
                  "
                />
              </div>

              <div className="flex items-center gap-3 pt-1">
                <button
                  type="button"
                  onClick={() => navigate(-1)}
                  className="
                    text-[13px] font-medium text-[#b0b0b0] hover:text-white
                    border border-[#2e2e2e] hover:border-[#424242]
                    bg-transparent hover:bg-[#1e1e1e]
                    px-4 py-2.5 rounded transition-all duration-150
                  "
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={!file || isUploading}
                  className="
                    bg-[#76ff03] hover:bg-[#65e000] active:bg-[#56cc00]
                    text-[#121212] font-semibold text-[13px]
                    px-4 py-2.5 rounded
                    transition-colors duration-150
                    disabled:opacity-50 disabled:cursor-not-allowed
                  "
                >
                  {isUploading ? (
                    <span className="flex items-center gap-2">
                      <svg className="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeDasharray="32" strokeDashoffset="8"/>
                      </svg>
                      Uploading…
                    </span>
                  ) : 'Upload Evidence'}
                </button>
              </div>
            </form>
          </>
        )}
      </div>
    </Layout>
  )
}
