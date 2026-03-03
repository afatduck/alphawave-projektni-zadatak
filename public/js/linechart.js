
const LINE_COLOR = "#FFB900"
const LINE_WIDTH = 3
const Y_MARGIN = 10

/**
 * @param {HTMLCanvasElement} element
 * @param {{value: number, time: string}[]} data
 */

function initChart(element, data) {
    const toolip = element.querySelector("p")
    const canvas = element.querySelector("canvas");
     /** @type {CanvasRenderingContext2D} */
    const ctx = canvas.getContext("2d");
    const height = canvas.height;
    const width = canvas.width;

    const dpr = window.devicePixelRatio || 1;

    canvas.width = canvas.offsetWidth * dpr;
    canvas.height = canvas.offsetHeight * dpr;

    ctx.scale(dpr, dpr);

    canvas.style.width = canvas.offsetWidth / dpr + 'px';
    canvas.style.height = canvas.offsetHeight / dpr + 'px';

    function drawLine(x1, y1, x2, y2) {
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.strokeStyle = LINE_COLOR;
        ctx.lineWidth = LINE_WIDTH;
        ctx.stroke();
    }

    const temps = data.map(d => d.temperature)
    const hours = data.map(d => (parseInt(d.time) - new Date().getTimezoneOffset() / 60) % 24)

    const maxTemp = Math.max(...temps);
    const minTemp = Math.min(...temps);
    const tempDif = maxTemp - minTemp;
    
    const positions = []

    temps.forEach((temp, i) => {
        const x = width * i / temps.length;
        const y = (1 - (temp - minTemp) / tempDif) * (height - Y_MARGIN) + Y_MARGIN * .5;
        positions.push([x, y])
    })

    function draw_graph() {
        for(let i = 1; i < positions.length; i++) {
            let [prevX, prevY] = positions[i-1]
            let [currentX, currentY] = positions[i]
            drawLine(prevX, prevY, currentX, currentY)
        }
    }

    draw_graph()

    element.addEventListener('mouseenter', function(e) {
        toolip.style.display = "block";
    });

    element.addEventListener('mouseleave', function(e) {
        toolip.style.display = "none";
        ctx.clearRect(0, 0, width, height)
        draw_graph()
    });   

    element.addEventListener('mousemove', function(e) {
        ctx.clearRect(0, 0, width, height)
        draw_graph()
        const index = Math.trunc(e.offsetX / (width + 1) * data.length)

        toolip.innerHTML = `${hours[index]}:00 ${temps[index]}°c`
        const [x, y] = positions[index]
        toolip.style.bottom = height - e.offsetY - 5 + "px"
        toolip.style.left = e.offsetX + 5 + "px"
        ctx.beginPath()
        ctx.arc(x, y, 6, 0, Math.PI * 2);
        ctx.fillStyle = "#0002";
        ctx.fill()

        ctx.beginPath()
        ctx.arc(x, y, 3, 0, Math.PI * 2);
        ctx.fillStyle = "black";
        ctx.fill()
    });   

}