<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click="closeModal">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" @click.stop>
      <!-- Header -->
      <div class="flex justify-between items-center p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">
          {{ mode === 'add' ? 'Add New Track' : 'Edit Track' }}
        </h2>
        <button 
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 text-2xl font-light hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors"
        >
          ×
        </button>
      </div>

      <!-- Form -->
      <div class="p-6">
        <TrackForm
          :track="track"
          :mode="mode"
          :loading="loading"
          :api-error="apiError"
          @submit="handleSubmit"
          @cancel="closeModal"
          @clear-error="apiError = ''"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TrackForm from './TrackForm.vue'
import { trackService } from '../services/trackService'

// Props
const props = defineProps({
  track: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    required: true,
    validator: (value) => ['add', 'edit'].includes(value)
  }
})

// Emits
const emit = defineEmits(['close', 'saved'])

// Reactive data
const loading = ref(false)
const apiError = ref('')

// Handle form submission
const handleSubmit = async (trackData) => {
  loading.value = true
  apiError.value = ''
  
  try {
    if (props.mode === 'add') {
      await trackService.createTrack(trackData)
    } else {
      await trackService.updateTrack(props.track.id, trackData)
    }
    
    emit('saved')
  } catch (error) {
    if (error.response?.data?.error) {
      apiError.value = error.response.data.error
    } else if (error.response?.data?.details) {
      // Handle validation errors from backend
      const backendErrors = error.response.data.details
      if (Array.isArray(backendErrors)) {
        apiError.value = backendErrors.join(', ')
      } else {
        apiError.value = 'Validation failed'
      }
    } else {
      apiError.value = error.message || 'An error occurred while saving the track'
    }
  } finally {
    loading.value = false
  }
}

// Close modal
const closeModal = () => {
  emit('close')
}
</script> 