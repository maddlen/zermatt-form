/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */
export default {
    success: false,
    submitted: false,
    form: null,
    formData: {},
    init () {
        if (Zermatt.Variables.formKey) {
            this.buildForm()
        } else {
            Zermatt.Event.listen('zermatt:form:key:init', this.buildForm.bind(this))
        }
    },
    buildForm () {
        const form = this.$el.querySelector('form')
        this.formData.form_key = Zermatt.Variables.formKey
        this.form = this.$form('post', form.getAttribute('action'), this.formData)
    },
    onSuccess (response) {
        window.location.href = response.data.redirect
    },
    validate (field) {
        this.form.errors = {}
        this.form.validate(field)
    },
    submit () {
        this.submitted = true
        this.form.submit()
            .then(response => {
                this.form.reset()
                this.success = true
                this.submitted = false
                this.onSuccess(response)
                setTimeout(() => {
                    this.success = false
                }, 4500)
            })
            .then(() => this.form.scrollIntoView())
            .catch(error => {
                console.log('Form has errors and was not submitted.')
            })
    }
}
