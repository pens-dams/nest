export const dateTimeFormat = datetimeString => (new Intl.DateTimeFormat('id-ID', {
  dateStyle: 'long',
  timeStyle: 'short',
})).format(new Date(datetimeString))
