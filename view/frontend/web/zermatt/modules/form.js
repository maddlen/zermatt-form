/*
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */
export default {
    domForm: null,
    form: null,
    formData: {},
    validating: false,
    submitting: false,
    init () {
        if (Zermatt.Variables.formKey) {
            this.buildForm()
        } else {
            Zermatt.Event.once('zermatt:form:key:init', this.buildForm.bind(this))
        }
    },
    buildForm (formData = null) {
        const formKey = Zermatt.Variables.formKey
        this.domForm = this.$el.querySelector('form') || this.$el.closest('form')
        this.formData.form_key = formKey
        if (formData) formData.form_key = formKey
        this.formData.must_redirect = Boolean(!this.onSubmitted())
        this.form = this.$form('post', this.domForm.getAttribute('action'), formData || this.formData)
    },
    onSubmitted (payload = {}) {
        return false
    },
    whenSubmitted (response) {
        if (!this.onSubmitted()) {
            this.submitting = true
            window.location.href = response.headers['x-zermatt-redirect']
        }
        if (response.data.success) {
            this.domForm.reset()
        }
        this.onSubmitted({ response: response.data, form: this.form.data() })
    },
    validate (field) {
        this.form.errors = {}
        this.form.validate(field)
    },
    async validateForm () {
        this.validating = true
        const formData = Object.assign({}, this.formData, this.form.data())
        formData.must_submit = false
        this.buildForm(formData)
        try {
            await this.form.submit()
            this.validating = false
            return true
        } catch (error) {
            console.log('Form has errors and was not submitted.')
            this.validating = false
            return false
        }
    },
    async submitForm () {
        this.submitting = true
        const formData = this.form.data()
        formData.must_submit = true
        this.buildForm(formData)
        this.form
            .submit()
            .then((response) => {
                this.submitting = false
                this.whenSubmitted(response)
                setTimeout(() => {
                    this.success = false
                }, 4500)
            })
            .catch((error) => {
                console.error(error)
                this.submitting = false
                console.log('Form has errors and was not submitted.')
            })
    },
    async submit () {
        const isValid = await this.validateForm()
        if (isValid) {
            await this.submitForm()
        }
    }
}
