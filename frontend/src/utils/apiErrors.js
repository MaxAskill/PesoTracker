export const assistantErrorMessage = (error) => {
  const status = error.response?.status
  const message = error.response?.data?.message

  if (status === 401) return 'Please log in again to use the AI Assistant.'
  if (status === 429) return message || 'Too many AI requests. Please wait before trying again.'
  if (status === 500) return message || 'AI Assistant server error. Please try again later.'
  if (status === 502) return message || 'AI provider error. Please try again later.'
  if (status === 503) return message || 'AI Assistant is currently unavailable.'

  return message || 'Sorry, I could not answer that right now. Please try again.'
}
