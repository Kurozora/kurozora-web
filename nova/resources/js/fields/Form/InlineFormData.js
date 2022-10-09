import isNil from 'lodash/isNil'

export default class InlineFormData {
  constructor(attribute, formData) {
    this.attribute = attribute
    this.formData = formData
    this.localFormData = new FormData()
  }

  append(name, ...args) {
    this.localFormData.append(name, ...args)
    this.formData.append(this.name(name), ...args)
  }

  delete(name) {
    this.localFormData.delete(name)
    this.formData.delete(this.name(name))
  }

  entries() {
    return this.localFormData.entries()
  }

  get(name) {
    return this.localFormData.get(name)
  }

  getAll(name) {
    return this.localFormData.getAll(name)
  }

  has(name) {
    return this.localFormData.has(name)
  }

  keys() {
    return this.localFormData.keys()
  }

  set(name, ...args) {
    this.localFormData.set(name, ...args)
    this.formData.set(this.name(name), ...args)
  }

  values() {
    return this.localFormData.values()
  }

  name(attribute) {
    let [name, ...nested] = attribute.split('[')

    if (!isNil(nested) && nested.length > 0) {
      return `${this.attribute}[${name}][${nested.join('[')}`
    }

    return `${this.attribute}[${attribute}]`
  }
}
