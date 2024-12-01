/*
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */
export default {
  success: false,
  domForm: null,
  form: null,
  formData: {},
  init() {
    if (Zermatt.Variables.formKey) {
      this.buildForm();
    } else {
      Zermatt.Event.once("zermatt:form:key:init", this.buildForm.bind(this));
    }
  },
  buildForm() {
    this.domForm = this.$el.querySelector("form");
    this.formData.form_key = Zermatt.Variables.formKey;
    this.formData.must_redirect = Boolean(!this.onSuccess());
    this.form = this.$form("post", this.domForm.getAttribute("action"), this.formData);
  },
  onSuccess() {
    return false;
  },
  doOnSuccess(response) {
    if (!this.onSuccess()) {
      window.location.href = response.headers["x-zermatt-redirect"];
    }
    this.onSuccess();
  },
  validate(field) {
    this.form.errors = {};
    this.form.validate(field);
  },
  submit() {
    this.form
      .submit()
      .then((response) => {
        this.form.reset();
        this.success = true;
        this.doOnSuccess(response);
        setTimeout(() => {
          this.success = false;
        }, 4500);
      })
      .then(() => this.domForm.scrollIntoView())
      .catch((error) => {
        console.log("Form has errors and was not submitted.");
      });
  },
};
