export const dateTimeFormat = (datetimeString: string): string => (new Intl.DateTimeFormat('id-ID', {
  dateStyle: 'long',
  timeStyle: 'short',
})).format(new Date(datetimeString))
