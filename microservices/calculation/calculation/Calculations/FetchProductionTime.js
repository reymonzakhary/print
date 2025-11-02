
/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class FetchProductionTime {

    /**
     *
     * @param machine
     * @param color
     * @param format
     * @param calculations
     */
    constructor(
        machine,
        color,
        format,
        calculations
    ) {
        this.machine = machine;
        this.color = color;
        this.format = format;
        this.calculations = calculations;
    }

    /**
     * Retrieves data related to machine setup and printing duration based on the selected color and quantity.
     *
     * @returns {{
     *     fed: string,
     *     duration_mpm: number,
     *     duration_type: string,
     *     machine_id: string,
     *     machine_name: string,
     *     duration_spm: number
     * }}
     */
    get()
    {
        let setup_time =  this.machine.setup_time;
        let cooling_time_spm = this.machine.cooling_time;
        let cooling_time_mpm = this.machine.cooling_time;
        let cooling_time_per = this.machine.cooling_time_per;
        let base_spm_duration = 0;
        let base_mpm_duration = 0;
        let used_color_id = String(this.color[0].option_id)
        let spm_duration = 0
        let mpm_duration = 0

        let machine_color = (this.machine.colors??[]).filter(clr => String(clr.mode_id) === used_color_id)

        spm_duration = this.calculations.amount_of_sheets_needed / machine_color[0]?.speed.spm ?? 0
        mpm_duration = this.calculations.amount_of_lm / machine_color[0]?.speed.mpm??0;

        base_spm_duration = setup_time + (this.format.quantity / spm_duration);
        base_mpm_duration = setup_time + (this.format.quantity / mpm_duration);

        if(cooling_time_per > 0) {
            cooling_time_spm = cooling_time_spm * Math.round(base_spm_duration / cooling_time_per);
            cooling_time_mpm = cooling_time_mpm * Math.round(base_mpm_duration / cooling_time_per);
        }

        let duration = 0;
        if(this.machine.fed === 'roll') {
            duration = Math.ceil(base_mpm_duration + cooling_time_mpm);
        }else {
            duration = Math.ceil(base_spm_duration + cooling_time_spm);
        }
        return {
            fed: this.machine.fed,
            machine_name: this.machine.name,
            machine_id: this.machine._id,
            duration: duration,
            duration_mpm: machine_color[0]?.speed.mpm,
            duration_spm: machine_color[0]?.speed.spm,
            duration_type: 'minutes',
        }
    }
}
