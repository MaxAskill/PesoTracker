const pesoFormatter = new Intl.NumberFormat('en-PH', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
})

export const formatPeso = (value) => {
  const amount = Number(value)

  if (!Number.isFinite(amount)) {
    return '₱0.00'
  }

  return `₱${pesoFormatter.format(amount)}`
}
