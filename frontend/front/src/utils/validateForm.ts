export function validateForm<T extends Record<string, string>>(
  validateObj: T,
): boolean {
  return Object.values(validateObj).every(
    (value) => value.trim().length > 0,
  )
}
