<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use app\models\LogFilter;


$this->title = 'Анализ логов';
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<div class="site-index">
    <h1><?= Html::encode($this->title) ?></h1>


    <div class="filter-form">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'options' => [
                'data-pjax' => true
            ],
        ]); ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($filterModel, 'date_from')->input('date') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($filterModel, 'date_to')->input('date') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($filterModel, 'os')->dropDownList(
                    ['' => 'Все'] + LogFilter::getOsList(),
                    ['prompt' => 'Выберите ОС']
                ) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($filterModel, 'architecture')->dropDownList(
                    ['' => 'Все'] + LogFilter::getArchitectureList(),
                    ['prompt' => 'Выберите архитектуру']
                ) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Применить фильтры', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Сбросить', ['index'], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

 
    <div class="row">
        <div class="col-md-6">
            <h3>Число запросов по датам</h3>
            <canvas id="requestsChart" width="400" height="200"></canvas>
        </div>
        <div class="col-md-6">
            <h3>Доля популярных браузеров (%)</h3>
            <canvas id="browserChart" width="400" height="200"></canvas>
        </div>
    </div>

 
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'timestamp',
                    'label' => 'Дата и время',
                    'format' => 'datetime',
                ],
                [
                    'attribute' => 'ip',
                    'label' => 'IP адрес',
                ],
                [
                    'attribute' => 'url',
                    'label' => 'URL',
                    'format' => 'url',
                ],
                [
                    'attribute' => 'browser',
                    'label' => 'Браузер',
                ],
                [
                    'attribute' => 'os',
                    'label' => 'Операционная система',
                ],
                [
                    'attribute' => 'architecture',
                    'label' => 'Архитектура',
                ],
                [
                    'attribute' => 'user_agent',
                    'label' => 'User Agent',
                    'value' => function ($model) {
                        return substr($model->user_agent, 0, 50) . '...';
                    },
                ],
            ],
        ]); ?>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {

    var chartLabels = <?= json_encode($chartData['labels']) ?>;
    var chartValues = <?= json_encode($chartData['values']) ?>;
    var browserLabels = <?= json_encode($browserChartData['labels']) ?>;
    var browserValues = <?= json_encode($browserChartData['values']) ?>;


    var requestsCtx = document.getElementById('requestsChart').getContext('2d');
    var requestsChart = new Chart(requestsCtx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Число запросов',
                data: chartValues,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Ось X – дата'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Ось Y - число запросов'
                    },
                    beginAtZero: true
                }
            }
        }
    });


    var browserCtx = document.getElementById('browserChart').getContext('2d');
    var browserChart = new Chart(browserCtx, {
        type: 'bar',
        data: {
            labels: browserLabels,
            datasets: [{
                label: 'Доля браузеров (%)',
                data: browserValues,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 205, 86, 0.2)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Ось X – дата'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Ось Y - % число запросов'
                    },
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
});
</script>