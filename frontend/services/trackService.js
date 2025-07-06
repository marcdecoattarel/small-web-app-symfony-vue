import axios from 'axios'

// Configure axios base URL to point to the Symfony backend
const API_BASE_URL = 'http://localhost:8000/api'

// Create axios instance with default configuration
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 10000, // 10 seconds timeout
})

// Request interceptor for logging (development only)
apiClient.interceptors.request.use(
  (config) => {
    console.log(` ${config.method?.toUpperCase()} ${config.url}`, config.data)
    return config
  },
  (error) => {
    console.error(' Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor for error handling
apiClient.interceptors.response.use(
  (response) => {
    console.log(`✅ ${response.config.method?.toUpperCase()} ${response.config.url}`, response.data)
    return response
  },
  (error) => {
    console.error('❌ Response error:', error.response?.data || error.message)
    
    // Enhance error messages for better UX
    if (error.response?.status === 404) {
      error.message = 'Resource not found'
    } else if (error.response?.status === 422) {
      error.message = 'Validation failed'
    } else if (error.response?.status >= 500) {
      error.message = 'Server error occurred'
    } else if (error.code === 'ECONNABORTED') {
      error.message = 'Request timed out'
    } else if (error.code === 'NETWORK_ERROR') {
      error.message = 'Network error - please check your connection'
    }
    
    return Promise.reject(error)
  }
)

/**
 * Track Service - Handles all track-related API operations
 */
export const trackService = {
  /**
   * Get all tracks
   * @returns {Promise<Array>} Array of track objects
   */
  async getTracks() {
    try {
      const response = await apiClient.get('/tracks')
      return response.data
    } catch (error) {
      throw new Error(`Failed to fetch tracks: ${error.message}`)
    }
  },

  /**
   * Get a single track by ID
   * @param {number} id - Track ID
   * @returns {Promise<Object>} Track object
   */
  async getTrack(id) {
    try {
      const response = await apiClient.get(`/tracks/${id}`)
      return response.data
    } catch (error) {
      throw new Error(`Failed to fetch track: ${error.message}`)
    }
  },

  /**
   * Create a new track
   * @param {Object} trackData - Track data object
   * @param {string} trackData.title - Track title
   * @param {string} trackData.artist - Artist name
   * @param {number} trackData.duration - Duration in seconds
   * @param {string|null} trackData.isrc - ISRC code (optional)
   * @returns {Promise<Object>} Created track object
   */
  async createTrack(trackData) {
    try {
      const response = await apiClient.post('/tracks', trackData)
      return response.data
    } catch (error) {
      // Handle validation errors from backend
      if (error.response?.data?.details) {
        const details = error.response.data.details
        if (Array.isArray(details)) {
          throw new Error(`Validation failed: ${details.join(', ')}`)
        }
      }
      throw new Error(`Failed to create track: ${error.message}`)
    }
  },

  /**
   * Update an existing track
   * @param {number} id - Track ID
   * @param {Object} trackData - Track data object (partial updates supported)
   * @returns {Promise<Object>} Updated track object
   */
  async updateTrack(id, trackData) {
    try {
      const response = await apiClient.put(`/tracks/${id}`, trackData)
      return response.data
    } catch (error) {
      // Handle validation errors from backend
      if (error.response?.data?.details) {
        const details = error.response.data.details
        if (Array.isArray(details)) {
          throw new Error(`Validation failed: ${details.join(', ')}`)
        }
      }
      throw new Error(`Failed to update track: ${error.message}`)
    }
  },

  /**
   * Delete a track (if implemented in backend)
   * @param {number} id - Track ID
   * @returns {Promise<void>}
   */
  async deleteTrack(id) {
    try {
      await apiClient.delete(`/tracks/${id}`)
    } catch (error) {
      throw new Error(`Failed to delete track: ${error.message}`)
    }
  },

  /**
   * Validate track data on the client side
   * @param {Object} trackData - Track data to validate
   * @returns {Object} Validation result with isValid boolean and errors array
   */
  validateTrackData(trackData) {
    const errors = []

    // Title validation
    if (!trackData.title || trackData.title.trim() === '') {
      errors.push('Title is required')
    }

    // Artist validation
    if (!trackData.artist || trackData.artist.trim() === '') {
      errors.push('Artist is required')
    }

    // Duration validation
    if (!trackData.duration || isNaN(trackData.duration) || trackData.duration <= 0) {
      errors.push('Duration must be a positive number')
    }

    // ISRC validation (optional)
    if (trackData.isrc && trackData.isrc.trim() !== '') {
      const isrcPattern = /^[A-Z]{2}-[A-Z0-9]{3}-[0-9]{2}-[0-9]{5}$/
      if (!isrcPattern.test(trackData.isrc)) {
        errors.push('ISRC must match the format: XX-XXX-XX-XXXXX')
      }
    }

    return {
      isValid: errors.length === 0,
      errors
    }
  },

  /**
   * Format duration from seconds to MM:SS format
   * @param {number} seconds - Duration in seconds
   * @returns {string} Formatted duration string
   */
  formatDuration(seconds) {
    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
  },

  /**
   * Parse duration from MM:SS format to seconds
   * @param {string} durationString - Duration string in MM:SS format
   * @returns {number} Duration in seconds
   */
  parseDuration(durationString) {
    const [minutes, seconds] = durationString.split(':').map(Number)
    return (minutes * 60) + (seconds || 0)
  }
}

// Export the API client for potential direct use
export { apiClient } 