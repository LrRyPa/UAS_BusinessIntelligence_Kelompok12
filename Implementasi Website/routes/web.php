<?php

return function (Router $r) {
    // Public
    $r->get('login',  [AuthController::class, 'showLogin']);
    $r->post('login', [AuthController::class, 'login']);
    $r->get('logout', [AuthController::class, 'logout']);

    // Dashboard
    $r->get('dashboard', [DashboardController::class, 'index']);
    $r->get('',          [DashboardController::class, 'index']);

    // Sales
    $r->get('sales/analytics', [SalesController::class, 'analytics']);
    $r->get('sales',           [SalesController::class, 'index']);
    $r->post('sales/store',    [SalesController::class, 'store']);
    $r->post('sales/update',   [SalesController::class, 'update']);
    $r->post('sales/delete',   [SalesController::class, 'delete']);

    // Products
    $r->get('products/analytics', [ProductController::class, 'analytics']);
    $r->get('products',           [ProductController::class, 'index']);
    $r->post('products/store',    [ProductController::class, 'store']);
    $r->post('products/update',   [ProductController::class, 'update']);
    $r->post('products/delete',   [ProductController::class, 'delete']);

    // Stores
    $r->get('stores/analytics', [StoreController::class, 'analytics']);
    $r->get('stores',           [StoreController::class, 'index']);
    $r->post('stores/store',    [StoreController::class, 'store']);
    $r->post('stores/update',   [StoreController::class, 'update']);
    $r->post('stores/delete',   [StoreController::class, 'delete']);

    // Inventory
    $r->get('inventory/analytics', [InventoryController::class, 'analytics']);
    $r->get('inventory',           [InventoryController::class, 'index']);
    $r->post('inventory/store',    [InventoryController::class, 'store']);
    $r->post('inventory/update',   [InventoryController::class, 'update']);
    $r->post('inventory/delete',   [InventoryController::class, 'delete']);

    // Users (admin)
    $r->get('users',         [UserController::class, 'index']);
    $r->post('users/store',  [UserController::class, 'store']);
    $r->post('users/update', [UserController::class, 'update']);
    $r->post('users/delete', [UserController::class, 'delete']);

    // Reports
    $r->get('reports',           [ReportController::class, 'index']);
    $r->get('reports/export',    [ReportController::class, 'exportCsv']);
    $r->get('reports/print',     [ReportController::class, 'exportPrint']);
};
