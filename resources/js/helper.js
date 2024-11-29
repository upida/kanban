import moment from "moment";
export function useHelper() {
    const formatDate = (date, form = "DD MMM YYYY") => {
        let format = form;
        const yearOfDate = moment(date).year();
        const yearNow = moment().year();

        if (yearOfDate == yearNow) {
            format = "MMM DD";
        }
        return moment(date).format(format);
    };
    return {
        formatDate,
    };
}
