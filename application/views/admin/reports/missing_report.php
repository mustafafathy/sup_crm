<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/tables.css'); ?>">

<?php if ($_GET['account'] == 2) blank_page('SOLAR MISSING REPORT IS COMING SOON :)'); ?>
<?php init_head(); ?>
<?php $today = date('Y-m-d'); ?>

<body>
    <div id="wrapper">
        <div class="content-wrapper">
            <div class="grid grid-cols-4 gap-5">
                <div class="reports-header col-span-3">
                    <h3>RE Summary</h3>
                </div>
                <div class="reports-header-actions grid grid-cols-2 tw-justify-between">
                    <button>
                        <i class="fa-solid fa-up-right-from-square"></i>
                        Export
                    </button>
                    <button>
                        <i class="fa-solid fa-camera"></i>
                        Screenshoot
                    </button>
                </div>
            </div>

            <div class="reports-filter-grid grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-8" style="display: grid;">
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(101, 186, 225, 1);">
                    Pending
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(50, 170, 199, 1);">
                    Qualified
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(8, 164, 167, 1);">
                    Disqualified
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(241, 202, 128, 1);">
                    IVE
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(248, 178, 2, 1);">
                    Pushed
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(251, 133, 0, 1);">
                    Callback
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(225, 84, 2, 1);">
                    Duplicated
                    <span id="" class="count">
                        0
                    </span>
                </div>
                <div class="card-element full-flex tw-flex-col tw-justify-between clickable-select" style="background-color: rgba(205, 51, 37, 1);">
                    Glitch
                    <span id="" class="count">
                        0
                    </span>
                </div>
            </div>
        </div>
        <div class="content-wrapper tickets-container grid lg:grid-cols-2 xl:grid-cols-3 gap-5">
            <div class="ticket" id="ticket_1">
                <div class="flex items-center justify-between p-4 ticket-header">
                    <h3>caesar</h3>
                    <div>active</div>
                </div>
                <div class="ticket-status">
                    <h3 class="m-2">status</h3>
                    <div>not achieved</div>
                </div>
                <div class="ticket-body mb-20">
                    <div class="main-states grid grid-cols-3 gap-3 justify-between p-4">
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                    </div>
                    <div class="more-states hidden">
                        <table class="w-full states-table">
                            <tbody>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="ticket-footer flex items-center py-3 w-full mt-8">
                    <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_1')">view more</button>
                </div>
            </div>
            <div class="ticket" id="ticket_2">
                <div class="flex items-center justify-between p-4 ticket-header">
                    <h3>caesar</h3>
                    <div>active</div>
                </div>
                <div class="ticket-status">
                    <h3 class="m-2">status</h3>
                    <div>not achieved</div>
                </div>
                <div class="ticket-body mb-20">
                    <div class="main-states grid grid-cols-3 gap-3 justify-between p-4">
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                    </div>
                    <div class="more-states hidden">
                        <table class="w-full states-table">
                            <tbody>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="ticket-footer flex items-center py-3 w-full">
                    <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_2')">view more</button>
                </div>
            </div>
            <div class="ticket" id="ticket_3">
                <div class="flex items-center justify-between p-4 ticket-header">
                    <h3>caesar</h3>
                    <div>active</div>
                </div>
                <div class="ticket-status">
                    <h3 class="m-2">status</h3>
                    <div>not achieved</div>
                </div>
                <div class="ticket-body mb-20">
                    <div class="main-states grid grid-cols-3 gap-3 justify-between p-4">
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ticket-footer flex items-center py-3 w-full mt-8">
                    <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_3')">view more</button>
                </div>
            </div>
            <div class="ticket" id="ticket_4">
                <div class="flex items-center justify-between p-4 ticket-header">
                    <h3>caesar</h3>
                    <div>active</div>
                </div>
                <div class="ticket-status">
                    <h3 class="m-2">status</h3>
                    <div>not achieved</div>
                </div>
                <div class="ticket-body mb-20">
                    <div class="main-states grid grid-cols-3 gap-3 justify-between p-4">
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ticket-footer flex items-center py-3 w-full mt-8">
                    <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_4')">view more</button>
                </div>
            </div>
            <div class="ticket" id="ticket_5">
                <div class="flex items-center justify-between p-4 ticket-header">
                    <h3>caesar</h3>
                    <div>active</div>
                </div>
                <div class="ticket-status">
                    <h3 class="m-2">status</h3>
                    <div>not achieved</div>
                </div>
                <div class="ticket-body mb-20">
                    <div class="main-states grid grid-cols-3 gap-3 justify-between p-4">
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                    </div>
                    <div class="more-states hidden">
                        <table class="w-full states-table">
                            <tbody>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                                <tr class="state-row">
                                    <td class="state-title px-4 py-1">weight</td>
                                    <td class="state-value px-3 py-1">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="ticket-footer flex items-center py-3 w-full mt-8">
                    <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_5')">view more</button>
                </div>
            </div>
            <div class="ticket" id="ticket_6">
                <div class="flex items-center justify-between p-4 ticket-header">
                    <h3>caesar</h3>
                    <div>active</div>
                </div>
                <div class="ticket-status">
                    <h3 class="m-2">status</h3>
                    <div>not achieved</div>
                </div>
                <div class="ticket-body mb-20">
                    <div class="main-states grid grid-cols-3 gap-3 justify-between p-4">
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                        <div class="text-center py-2">
                            <div class="title m-.5">
                                target
                            </div>
                            <div class="value">
                                4
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ticket-footer flex items-center py-3 w-full mt-8">
                    <button type="button" class="text-white bg-sky-700 font-medium rounded-lg text-sm px-9 py-2 mx-auto" onclick="ticketToggler('ticket_6')">view more</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<style>
    .content-wrapper {
        margin: 43px;
        min-width: 320px;
    }

    .reports-header {
        background-color: #FFFFFF;
        padding: 12px 20px;
        border-radius: 4px;
        margin-bottom: 12px;
    }

    .reports-header h3 {
        font-size: 18px;
        font-weight: 500;
        font-family: "Poppins";
        margin: 0 !important;
    }

    .reports-header-actions {
        margin-bottom: 12px;
        gap: 20px;
    }

    .reports-header-actions button {
        color: #FFFFFF;
        background-color: rgba(11, 112, 163, 1);
        border-radius: 4px;
        font-size: 16px;
        font-weight: 500;
    }

    .reports-filter-grid {
        padding: 0;
        gap: 10px;
    }

    .card-element {
        color: #FFFFFF;
    }

    .tickets-container {
        font-family: 'Poppins';
        text-transform: capitalize;
        transition: all .3s ease-in;
    }

    .ticket {
        min-width: 317px;
        background-color: #FFFFFF;
        border-radius: 12px;
        border: 1px solid rgba(222, 222, 222, 1);
        box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
        transition: all .1s ease-in;
        position: relative;
    }

    .ticket:hover,
    .ticket.row-span-2 {
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    }

    .ticket-header {
        border-bottom: 1px solid #DEDEDE;
        padding: 11px 16px;
    }

    .ticket-header h3 {
        font-size: 20px;
        font-weight: 500;
        line-height: 100%;
        letter-spacing: -1px;
    }

    .ticket-header div {
        font-weight: 400;
        font-size: 14px;
        line-height: 100%;
        letter-spacing: 0;
        padding: 9px 13px;
        color: #34A770;
        background-color: #DCF3EB;
        border-radius: 6px;
    }

    .ticket-status {
        margin: 31px;
        line-height: 100%;
        letter-spacing: -1px;
        text-align: center;
    }

    .ticket-status h3 {
        color: #625F68;
        font-weight: 400;
        font-size: 16px;
        margin: 7px !important;
    }

    .ticket-status div {
        color: #A8A6AC;
        font-weight: 400;
        font-size: 16px;
    }

    .ticket-body {
        margin-bottom: 80px;
    }

    .main-states {
        padding: 16px;
    }

    .main-states>div {
        background-color: #F9F9F9;
        border: 1px solid #BCBCBC;
        border-radius: 6px;
        padding: 7px;
    }

    .ticket-body .title {
        color: rgba(0, 0, 0, 0.4);
        font-weight: 400;
        font-size: 14px;
        margin: 2px;
    }

    .ticket-body .value {
        font-weight: 600;
        font-size: 16px;
    }

    .more-states {
        max-height: calc(11 * 32.5px);
        overflow-y: auto;
        margin-top: 17px;
    }

    .state-row .state-title {
        color: #625F68;
        padding: 4px 16px;
    }

    .state-row .state-value {
        color: #A8A6AC;
        padding: 4px 12px;
    }

    .states-table tr:nth-child(even) {
        background-color: #F9F9F9;
        border-top: 0.5px solid rgba(188, 188, 188, 0.8);
        border-bottom: 0.5px solid rgba(188, 188, 188, 0.8);
    }

    .ticket-footer {
        border-top: 1px solid #DEDEDE;
        position: absolute;
        bottom: 0;
        padding: 12px 0;
        justify-content: center;
        margin-top: 32px;
    }

    .ticket-footer button {
        background-color: rgba(11, 112, 163, 1);
        color: #FFFFFF;
        font-size: 16px !important;
        font-weight: 600;
        text-transform: capitalize;
        border-radius: 4px;
        padding: 7px 36px;
        line-height: 24px;
        letter-spacing: 1;
        box-shadow: #0A0D120D 0px 1px 2px 0px;
        cursor: pointer;
    }

    .ticket-footer button:hover {
        transform: translateY(-1px);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script>
    function ticketToggler(ticketId) {
        const targetedTicket = document.getElementById(ticketId);
        const toggleButton = document.getElementById(ticketId).getElementsByTagName("button")[0];
        const ticketTable = document.getElementById(ticketId).getElementsByClassName("more-states")[0];
        //  targetedTicket.classList.toggle("ticket-active");
        if (ticketTable) {
            targetedTicket.classList.toggle("row-span-2");
            toggleButton.innerText = "view less";
            ticketTable.classList.toggle("hidden");
        }
    }
</script>