<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Title Field -->
    <div>
      <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
        Title *
      </label>
      <input
        id="title"
        v-model="form.title"
        type="text"
        :class="[
          'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
          errors.title ? 'border-red-500 bg-red-50' : 'border-gray-300'
        ]"
        placeholder="Enter track title"
        required
      />
      <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
    </div>

    <!-- Artist Field -->
    <div>
      <label for="artist" class="block text-sm font-medium text-gray-700 mb-2">
        Artist *
      </label>
      <input
        id="artist"
        v-model="form.artist"
        type="text"
        :class="[
          'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
          errors.artist ? 'border-red-500 bg-red-50' : 'border-gray-300'
        ]"
        placeholder="Enter artist name"
        required
      />
      <p v-if="errors.artist" class="mt-1 text-sm text-red-600">{{ errors.artist }}</p>
    </div>

    <!-- Duration Field -->
    <div>
      <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
        Duration (seconds) *
      </label>
      <input
        id="duration"
        v-model.number="form.duration"
        type="number"
        min="1"
        :class="[
          'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
          errors.duration ? 'border-red-500 bg-red-50' : 'border-gray-300'
        ]"
        placeholder="Enter duration in seconds"
        required
      />
      <p v-if="errors.duration" class="mt-1 text-sm text-red-600">{{ errors.duration }}</p>
      <p class="mt-1 text-xs text-gray-500">Enter duration in seconds (e.g., 180 for 3 minutes)</p>
    </div>

    <!-- ISRC Field -->
    <div>
      <label for="isrc" class="block text-sm font-medium text-gray-700 mb-2">
        ISRC (Optional)
      </label>
      <input
        id="isrc"
        v-model="form.isrc"
        type="text"
        maxlength="15"
        :class="[
          'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
          errors.isrc ? 'border-red-500 bg-red-50' : 'border-gray-300'
        ]"
        placeholder="XX-XXX-XX-XXXXX"
      />
      <p v-if="errors.isrc" class="mt-1 text-sm text-red-600">{{ errors.isrc }}</p>
      <p class="mt-1 text-xs text-gray-500">Format: XX-XXX-XX-XXXXX (e.g., GB-EMI-76-12345)</p>
    </div>

    <!-- Form Actions -->
    <div class="flex gap-3 pt-4">
      <button 
        type="button" 
        @click="$emit('cancel')"
        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
      >
        Cancel
      </button>
      <button 
        type="submit" 
        :disabled="loading"
        class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
        {{ submitText }}
      </button>
    </div>

    <!-- API Error -->
    <div v-if="apiError" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex items-center">
        <span class="text-red-500 mr-2">⚠️</span>
        <p class="text-sm text-red-700">{{ apiError }}</p>
      </div>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue'

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
  },
  loading: {
    type: Boolean,
    default: false
  },
  apiError: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['submit', 'cancel'])

// Reactive data
const errors = reactive({})

const form = reactive({
  title: '',
  artist: '',
  duration: '',
  isrc: ''
})

// Computed
const submitText = computed(() => {
  return props.mode === 'add' ? 'Add Track' : 'Update Track'
})

// Validation rules
const validationRules = {
  title: (value) => {
    if (!value || value.trim() === '') return 'Title is required'
    if (value.trim().length < 1) return 'Title must not be empty'
    return null
  },
  artist: (value) => {
    if (!value || value.trim() === '') return 'Artist is required'
    if (value.trim().length < 1) return 'Artist must not be empty'
    return null
  },
  duration: (value) => {
    if (!value || value === '') return 'Duration is required'
    if (isNaN(value) || value <= 0) return 'Duration must be a positive number'
    return null
  },
  isrc: (value) => {
    if (!value || value.trim() === '') return null // ISRC is optional
    const isrcPattern = /^[A-Z]{2}-[A-Z0-9]{3}-[0-9]{2}-[0-9]{5}$/
    if (!isrcPattern.test(value)) {
      return 'ISRC must match the format: XX-XXX-XX-XXXXX'
    }
    return null
  }
}

// Validate form
const validateForm = () => {
  const newErrors = {}
  
  Object.keys(validationRules).forEach(field => {
    const error = validationRules[field](form[field])
    if (error) {
      newErrors[field] = error
    }
  })
  
  Object.assign(errors, newErrors)
  return Object.keys(newErrors).length === 0
}

// Watch for form changes and clear errors
watch(form, () => {
  if (props.apiError) {
    emit('clear-error')
  }
}, { deep: true })

// Initialize form with track data (for edit mode)
onMounted(() => {
  if (props.track && props.mode === 'edit') {
    Object.assign(form, {
      title: props.track.title || '',
      artist: props.track.artist || '',
      duration: props.track.duration || '',
      isrc: props.track.isrc || ''
    })
  }
})

// Handle form submission
const handleSubmit = () => {
  if (!validateForm()) {
    return
  }
  
  const trackData = {
    title: form.title.trim(),
    artist: form.artist.trim(),
    duration: parseInt(form.duration),
    isrc: form.isrc.trim() || null
  }
  
  emit('submit', trackData)
}
</script> 