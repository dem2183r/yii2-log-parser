<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\LogEntry;
use app\models\LogFilter;

class SiteController extends Controller
{
    /**
     * Главная страница с отображением данных, фильтрами и графиками
     */
    public function actionIndex()
    {
        $filterModel = new LogFilter();
        $filterModel->load(Yii::$app->request->get());


        $query = LogEntry::find();


        $query->andFilterWhere(['>=', 'timestamp', $filterModel->date_from ? date('Y-m-d H:i:s', strtotime($filterModel->date_from)) : null])
              ->andFilterWhere(['<=', 'timestamp', $filterModel->date_to ? date('Y-m-d H:i:s', strtotime($filterModel->date_to)) : null])
              ->andFilterWhere(['os' => $filterModel->os])
              ->andFilterWhere(['architecture' => $filterModel->architecture]);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'timestamp',
                    'ip',
                    'url',
                    'browser',
                    'os',
                    'architecture',
                ],
                'defaultOrder' => [
                    'timestamp' => SORT_DESC,
                ],
            ],
        ]);

        $chartData = $this->getChartData($query);
        $browserChartData = $this->getBrowserChartData($query);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'filterModel' => $filterModel,
            'chartData' => $chartData,
            'browserChartData' => $browserChartData,
        ]);
    }

    /**
     * Получение данных для графика "Число запросов по датам"
     */
    private function getChartData($baseQuery)
    {

        $query = clone $baseQuery;
        
        $data = $query->select([
                'DATE(timestamp) as date',
                'COUNT(*) as count'
            ])
            ->groupBy(['DATE(timestamp)'])
            ->orderBy(['date' => SORT_ASC])
            ->asArray()
            ->all();

        $labels = [];
        $values = [];
        
        foreach ($data as $item) {
            $labels[] = $item['date'];
            $values[] = (int)$item['count'];
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Получение данных для графика "Доля популярных браузеров"
     */
    private function getBrowserChartData($baseQuery)
    {

        $query = clone $baseQuery;

        $totalCount = $query->count();
        
        if ($totalCount == 0) {
            return [
                'labels' => [],
                'values' => []
            ];
        }

        $browserData = $query->select([
                'browser',
                'COUNT(*) as count'
            ])
            ->groupBy(['browser'])
            ->orderBy(['count' => SORT_DESC])
            ->limit(3)
            ->asArray()
            ->all();

        $labels = [];
        $values = [];
        
        foreach ($browserData as $item) {
            $labels[] = $item['browser'];
            $values[] = round(($item['count'] / $totalCount) * 100, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Страница "О проекте"
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}